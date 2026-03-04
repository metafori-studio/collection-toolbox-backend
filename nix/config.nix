{
  dataDir = ".nix-data";
  postgresDataDir = ".nix-data/postgres";
  valkeyDataDir = ".nix-data/valkey";
  otelDataDir = ".nix-data/otel";
  grafanaDataDir = ".nix-data/grafana";
  tempoDataDir = ".nix-data/tempo";
  prometheusDataDir = ".nix-data/prometheus";
  lokiDataDir = ".nix-data/loki";
  seaweedfsDataDir = ".nix-data/seaweedfs";
  seaweedfsAdminDataDir = ".nix-data/seaweedfs-admin";
  s3CredentialsFile = ".nix-data/s3_credentials.json";

  postgresPort = "5432";
  postgresUser = "postgres";
  postgresDb = "collection_toolbox_backend";

  valkeyPort = "6379";
  grafanaPort = "3001";
  tempoPort = "3200";
  prometheusPort = "9090";
  lokiPort = "3100";
  lokiGrpcPort = "9096";
  seaweedfsPort = "8333";
  seaweedfsMasterPort = "9333";
  seaweedfsVolumePort = "8080";
  seaweedfsFilerPort = "8833";
  seaweedfsMetricsPort = "9327";
  seaweedfsAdminPort = "23646";

  otelOtlpGrpcPort = "4317";
  otelOtlpHttpPort = "4318";
  otelMetricsPort = "8889";
  otelHealthCheckPort = "13133";
  otelPprofPort = "1777";
  otelZpagesPort = "55679";
  tempoOtlpGrpcPort = "4319";
  tempoOtlpHttpPort = "4320";
}
