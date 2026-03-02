{
  pkgs,
  config,
}:
let
  inherit (config)
    otelDataDir
    grafanaDataDir
    tempoDataDir
    prometheusDataDir
    lokiDataDir
    grafanaPort
    tempoPort
    prometheusPort
    lokiPort
    ;

  pkgs_otel = pkgs."opentelemetry-collector-contrib";
  pkgs_grafana = pkgs.grafana;
  pkgs_tempo = pkgs.tempo;
  pkgs_prometheus = pkgs.prometheus;
  pkgs_loki = pkgs.grafana-loki;

  wait_for = name: url: ''
    echo -n "Waiting for ${name} to be ready..."
    count=0
    until ${pkgs.curl}/bin/curl -s "${url}" > /dev/null 2>&1; do
      sleep 1
      count=$((count + 1))
      if [ $count -ge 30 ]; then
        echo " TIMEOUT"
        return 1
      fi
    done
    echo " READY"
  '';

  stop_service = name: pidfile: ''
    if [ -f "${pidfile}" ]; then
      echo "Stopping ${name}..."
      kill $(cat "${pidfile}") > /dev/null 2>&1 || true
      rm -f "${pidfile}"
    fi
  '';

  startMonitoring = pkgs.writeShellScriptBin "start-monitoring" ''
    mkdir -p ${otelDataDir} ${grafanaDataDir} ${tempoDataDir} ${prometheusDataDir} ${lokiDataDir}

    PROJ_ROOT="$(pwd)"

    # Prometheus
    if [ ! -f "$PROJ_ROOT/${prometheusDataDir}/prometheus.pid" ] || ! kill -0 $(cat "$PROJ_ROOT/${prometheusDataDir}/prometheus.pid") > /dev/null 2>&1; then
      ${pkgs_prometheus}/bin/prometheus \
        --config.file="$PROJ_ROOT/nix/prometheus-config.yaml" \
        --storage.tsdb.path="$PROJ_ROOT/${prometheusDataDir}" \
        --web.listen-address=0.0.0.0:${prometheusPort} \
        --web.enable-remote-write-receiver \
        > "$PROJ_ROOT/${prometheusDataDir}/prometheus.log" 2>&1 &
      echo $! > "$PROJ_ROOT/${prometheusDataDir}/prometheus.pid"
      ${wait_for "Prometheus" "http://127.0.0.1:${prometheusPort}/-/ready"}
    fi

    # Tempo
    if [ ! -f "$PROJ_ROOT/${tempoDataDir}/tempo.pid" ] || ! kill -0 $(cat "$PROJ_ROOT/${tempoDataDir}/tempo.pid") > /dev/null 2>&1; then
      ${pkgs_tempo}/bin/tempo \
        -config.file="$PROJ_ROOT/nix/tempo-config.yaml" \
        -config.expand-env=true \
        > "$PROJ_ROOT/${tempoDataDir}/tempo.log" 2>&1 &
      echo $! > "$PROJ_ROOT/${tempoDataDir}/tempo.pid"
      ${wait_for "Tempo" "http://127.0.0.1:${tempoPort}/ready"}
    fi

    # Loki
    if [ ! -f "$PROJ_ROOT/${lokiDataDir}/loki.pid" ] || ! kill -0 $(cat "$PROJ_ROOT/${lokiDataDir}/loki.pid") > /dev/null 2>&1; then
      ${pkgs_loki}/bin/loki \
        -config.file="$PROJ_ROOT/nix/loki-config.yaml" \
        -config.expand-env=true \
        > "$PROJ_ROOT/${lokiDataDir}/loki.log" 2>&1 &
      echo $! > "$PROJ_ROOT/${lokiDataDir}/loki.pid"
      ${wait_for "Loki" "http://127.0.0.1:${lokiPort}/ready"}
    fi

    # otel-collector
    if [ ! -f "$PROJ_ROOT/${otelDataDir}/otel.pid" ] || ! kill -0 $(cat "$PROJ_ROOT/${otelDataDir}/otel.pid") > /dev/null 2>&1; then
      ${pkgs_otel}/bin/otelcol-contrib \
        --config "$PROJ_ROOT/nix/otel-config.yaml" \
        > "$PROJ_ROOT/${otelDataDir}/otel.log" 2>&1 &
      echo $! > "$PROJ_ROOT/${otelDataDir}/otel.pid"
      ${wait_for "OTel Collector" "http://127.0.0.1:13133/"}
    fi

    # Grafana
    if [ ! -f "$PROJ_ROOT/${grafanaDataDir}/grafana.pid" ] || ! kill -0 $(cat "$PROJ_ROOT/${grafanaDataDir}/grafana.pid") > /dev/null 2>&1; then
      ${pkgs_grafana}/bin/grafana server \
        --config "$PROJ_ROOT/nix/grafana-config.ini" \
        --homepath "${pkgs_grafana}/share/grafana" \
        cfg:default.paths.data="$PROJ_ROOT/${grafanaDataDir}" \
        cfg:default.paths.logs="$PROJ_ROOT/${grafanaDataDir}/logs" \
        cfg:default.paths.plugins="$PROJ_ROOT/${grafanaDataDir}/plugins" \
        cfg:default.paths.provisioning="$PROJ_ROOT/nix/provisioning" \
        > "$PROJ_ROOT/${grafanaDataDir}/grafana.log" 2>&1 &
      echo $! > "$PROJ_ROOT/${grafanaDataDir}/grafana.pid"
      ${wait_for "Grafana" "http://127.0.0.1:${grafanaPort}/api/health"}
    fi

    echo "Monitoring stack accessible at:"
    echo "  Grafana: http://localhost:${grafanaPort}"
    echo "  Prometheus: http://localhost:${prometheusPort}"
    echo "  Tempo: http://localhost:${tempoPort}"
    echo "  Loki: http://localhost:${lokiPort}"
  '';

  stopMonitoring = pkgs.writeShellScriptBin "stop-monitoring" ''
    PROJ_ROOT="$(pwd)"
    ${stop_service "Grafana" "$PROJ_ROOT/${grafanaDataDir}/grafana.pid"}
    ${stop_service "otel-collector" "$PROJ_ROOT/${otelDataDir}/otel.pid"}
    ${stop_service "Loki" "$PROJ_ROOT/${lokiDataDir}/loki.pid"}
    ${stop_service "Tempo" "$PROJ_ROOT/${tempoDataDir}/tempo.pid"}
    ${stop_service "Prometheus" "$PROJ_ROOT/${prometheusDataDir}/prometheus.pid"}
  '';
in
{
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
