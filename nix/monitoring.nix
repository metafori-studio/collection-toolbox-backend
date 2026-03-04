{
  pkgs,
  config,
}: let
  inherit
    (config)
    otelDataDir
    grafanaDataDir
    tempoDataDir
    prometheusDataDir
    lokiDataDir
    grafanaPort
    tempoPort
    prometheusPort
    lokiPort
    otelHealthCheckPort
    ;

  pkgs_otel = pkgs."opentelemetry-collector-contrib";
  pkgs_grafana = pkgs.grafana;
  pkgs_tempo = pkgs.tempo;
  pkgs_prometheus = pkgs.prometheus;
  pkgs_loki = pkgs.grafana-loki;

  wait_for = name: url: ''
    echo -n "Waiting for ${name} to be ready..."
    count=0
    until "${pkgs.curl}/bin/curl" -s "${url}" > /dev/null 2>&1; do
      sleep 1
      count=$((count + 1))
      if [ $count -ge 30 ]; then
        echo " TIMEOUT"
        exit 1
      fi
    done
    echo " READY"
  '';

  stop_service = name: pidfile: ''
    if [ -f "${pidfile}" ]; then
      PID=$(cat "${pidfile}")
      if kill -0 "$PID" 2>/dev/null; then
        echo "Stopping ${name} (PID: $PID)..."
        kill "$PID" 2>/dev/null || true

        # Wait for it to stop
        count=0
        while kill -0 "$PID" 2>/dev/null; do
          sleep 0.5
          count=$((count + 1))
          if [ $count -ge 10 ]; then
            echo "Forcefully killing ${name}..."
            kill -9 "$PID" 2>/dev/null || true
            break
          fi
        done
      fi
      rm -f "${pidfile}"
    fi
  '';

  # Import Config Templates
  prometheus_tmpl = import ./config/prometheus.nix {inherit config;};
  otel_tmpl = import ./config/otel.nix {inherit config;};
  tempo_tmpl = import ./config/tempo.nix {inherit config;};
  loki_tmpl = import ./config/loki.nix {inherit config;};
  grafana_tmpl = import ./config/grafana.nix {inherit config;};

  # Configuration Files
  configs = {
    prometheus = pkgs.writeText "prometheus.yaml" prometheus_tmpl;
    otel = pkgs.writeText "otel.yaml" otel_tmpl;
    tempo = pkgs.writeText "tempo.yaml" tempo_tmpl;
    loki = pkgs.writeText "loki.yaml" loki_tmpl;
    grafana_ini = pkgs.writeText "grafana.ini" grafana_tmpl.ini;
    grafana_datasources = pkgs.writeText "datasources.yaml" grafana_tmpl.datasources;
  };

  startMonitoring = pkgs.writeShellScriptBin "start-monitoring" ''
    PROJ_ROOT="''${PROJECT_ROOT:-$(pwd)}"
    mkdir -p "$PROJ_ROOT/${otelDataDir}" "$PROJ_ROOT/${grafanaDataDir}" "$PROJ_ROOT/${tempoDataDir}" "$PROJ_ROOT/${prometheusDataDir}" "$PROJ_ROOT/${lokiDataDir}"

    # Prometheus
    if [ ! -f "$PROJ_ROOT/${prometheusDataDir}/prometheus.pid" ] || ! kill -0 "$(cat "$PROJ_ROOT/${prometheusDataDir}/prometheus.pid")" > /dev/null 2>&1; then
      "${pkgs_prometheus}/bin/prometheus" \
        --config.file="${configs.prometheus}" \
        --storage.tsdb.path="$PROJ_ROOT/${prometheusDataDir}" \
        --web.listen-address="0.0.0.0:${toString prometheusPort}" \
        --web.enable-remote-write-receiver \
        > "$PROJ_ROOT/${prometheusDataDir}/prometheus.log" 2>&1 &
      echo $! > "$PROJ_ROOT/${prometheusDataDir}/prometheus.pid"
      ${wait_for "Prometheus" "http://127.0.0.1:${toString prometheusPort}/-/ready"}
    fi

    # Tempo
    if [ ! -f "$PROJ_ROOT/${tempoDataDir}/tempo.pid" ] || ! kill -0 "$(cat "$PROJ_ROOT/${tempoDataDir}/tempo.pid")" > /dev/null 2>&1; then
      "${pkgs_tempo}/bin/tempo" \
        -config.file="${configs.tempo}" \
        > "$PROJ_ROOT/${tempoDataDir}/tempo.log" 2>&1 &
      echo $! > "$PROJ_ROOT/${tempoDataDir}/tempo.pid"
      ${wait_for "Tempo" "http://127.0.0.1:${toString tempoPort}/ready"}
    fi

    # Loki
    if [ ! -f "$PROJ_ROOT/${lokiDataDir}/loki.pid" ] || ! kill -0 "$(cat "$PROJ_ROOT/${lokiDataDir}/loki.pid")" > /dev/null 2>&1; then
      "${pkgs_loki}/bin/loki" \
        -config.file="${configs.loki}" \
        > "$PROJ_ROOT/${lokiDataDir}/loki.log" 2>&1 &
      echo $! > "$PROJ_ROOT/${lokiDataDir}/loki.pid"
      ${wait_for "Loki" "http://127.0.0.1:${toString lokiPort}/ready"}
    fi

    # otel-collector
    if [ ! -f "$PROJ_ROOT/${otelDataDir}/otel.pid" ] || ! kill -0 "$(cat "$PROJ_ROOT/${otelDataDir}/otel.pid")" > /dev/null 2>&1; then
      "${pkgs_otel}/bin/otelcol-contrib" \
        --config "${configs.otel}" \
        > "$PROJ_ROOT/${otelDataDir}/otel.log" 2>&1 &
      echo $! > "$PROJ_ROOT/${otelDataDir}/otel.pid"
      ${wait_for "otel-collector" "http://127.0.0.1:${toString otelHealthCheckPort}/"}
    fi

    # Grafana
    if [ ! -f "$PROJ_ROOT/${grafanaDataDir}/grafana.pid" ] || ! kill -0 "$(cat "$PROJ_ROOT/${grafanaDataDir}/grafana.pid")" > /dev/null 2>&1; then
      # Create temporary provisioning directory
      mkdir -p "$PROJ_ROOT/${grafanaDataDir}/provisioning/datasources"
      mkdir -p "$PROJ_ROOT/${grafanaDataDir}/provisioning/dashboards"

      cp -f "${configs.grafana_datasources}" "$PROJ_ROOT/${grafanaDataDir}/provisioning/datasources/datasources.yaml"
      chmod +w "$PROJ_ROOT/${grafanaDataDir}/provisioning/datasources/datasources.yaml"

      # Generate the dashboards provider at runtime with the absolute path
      cat <<EOF > "$PROJ_ROOT/${grafanaDataDir}/provisioning/dashboards/all.yaml"
    apiVersion: 1
    providers:
      - name: 'Default'
        orgId: 1
        folder: ""
        type: file
        disableDeletion: false
        editable: true
        updateIntervalSeconds: 10
        allowUiUpdates: true
        options:
          path: $PROJ_ROOT/nix/grafana/dashboards
    EOF

      "${pkgs_grafana}/bin/grafana" server \
        --config "${configs.grafana_ini}" \
        --homepath "${pkgs_grafana}/share/grafana" \
        cfg:default.paths.data="$PROJ_ROOT/${grafanaDataDir}" \
        cfg:default.paths.logs="$PROJ_ROOT/${grafanaDataDir}/logs" \
        cfg:default.paths.plugins="$PROJ_ROOT/${grafanaDataDir}/plugins" \
        cfg:default.paths.provisioning="$PROJ_ROOT/${grafanaDataDir}/provisioning" \
        > "$PROJ_ROOT/${grafanaDataDir}/grafana.log" 2>&1 &
      echo $! > "$PROJ_ROOT/${grafanaDataDir}/grafana.pid"
      ${wait_for "Grafana" "http://127.0.0.1:${toString grafanaPort}/api/health"}
    fi

    echo "  Grafana: http://localhost:${toString grafanaPort}"
    echo "  Prometheus: http://localhost:${toString prometheusPort}"
    echo "  Tempo: http://localhost:${toString tempoPort}"
    echo "  Loki: http://localhost:${toString lokiPort}"
  '';

  stopMonitoring = pkgs.writeShellScriptBin "stop-monitoring" ''
    PROJ_ROOT="''${PROJECT_ROOT:-$(pwd)}"
    ${stop_service "Grafana" "$PROJ_ROOT/${grafanaDataDir}/grafana.pid"}
    ${stop_service "otel-collector" "$PROJ_ROOT/${otelDataDir}/otel.pid"}
    ${stop_service "Loki" "$PROJ_ROOT/${lokiDataDir}/loki.pid"}
    ${stop_service "Tempo" "$PROJ_ROOT/${tempoDataDir}/tempo.pid"}
    ${stop_service "Prometheus" "$PROJ_ROOT/${prometheusDataDir}/prometheus.pid"}
  '';
in {
  start = startMonitoring;
  stop = stopMonitoring;
  inherit
    pkgs_otel
    pkgs_grafana
    pkgs_tempo
    pkgs_prometheus
    pkgs_loki
    ;
}
