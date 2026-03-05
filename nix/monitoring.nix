{
  pkgs,
  config,
}:
let
  pkgs_otel = pkgs."opentelemetry-collector-contrib";
  pkgs_grafana = pkgs.grafana;
  pkgs_tempo = pkgs.tempo;
  pkgs_prometheus = pkgs.prometheus;
  pkgs_loki = pkgs.grafana-loki;

  # Import Config Templates
  prometheus_tmpl = import ./config/prometheus.nix { inherit config; };
  otel_tmpl = import ./config/otel.nix { inherit config; };
  tempo_tmpl = import ./config/tempo.nix { inherit config; };
  loki_tmpl = import ./config/loki.nix { inherit config; };
  grafana_tmpl = import ./config/grafana.nix { inherit config; };

  # Configuration Files
  configs = {
    prometheus = pkgs.writeText "prometheus.yaml" prometheus_tmpl;
    otel = pkgs.writeText "otel.yaml" otel_tmpl;
    tempo = pkgs.writeText "tempo.yaml" tempo_tmpl;
    loki = pkgs.writeText "loki.yaml" loki_tmpl;
    grafana_ini = pkgs.writeText "grafana.ini" grafana_tmpl.ini;
    grafana_datasources = pkgs.writeText "datasources.yaml" grafana_tmpl.datasources;
  };
in
{
  inherit
    pkgs_otel
    pkgs_grafana
    pkgs_tempo
    pkgs_prometheus
    pkgs_loki
    configs
    ;
}
