{
  dataDir = ".nix-data";
  postgresDataDir = ".nix-data/postgres";
  valkeyDataDir = ".nix-data/valkey";
  otelDataDir = ".nix-data/otel";
  grafanaDataDir = ".nix-data/grafana";
  tempoDataDir = ".nix-data/tempo";
  prometheusDataDir = ".nix-data/prometheus";
  lokiDataDir = ".nix-data/loki";

  postgresPort = "5432";
  postgresUser = "postgres";
  postgresDb = "collection_toolbox_backend";

  valkeyPort = "6379";
  grafanaPort = "3001";
  tempoPort = "3200";
  prometheusPort = "9090";
  lokiPort = "3100";
}
