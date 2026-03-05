{
  description = "collection_toolbox_backend dev environment";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
    flake-utils.url = "github:numtide/flake-utils";
  };

  outputs =
    {
      nixpkgs,
      flake-utils,
      ...
    }:
    flake-utils.lib.eachDefaultSystem (
      system:
      let
        pkgs = nixpkgs.legacyPackages.${system};
        lib = pkgs.lib;

        config = import ./nix/config.nix;
        php = import ./nix/php.nix { inherit pkgs; };
        databases = import ./nix/databases.nix { inherit pkgs config; };
        monitoring = import ./nix/monitoring.nix { inherit pkgs config; };
        storage = import ./nix/storage.nix { inherit pkgs config; };

      in
      {
        devShells.default = pkgs.mkShell {
          buildInputs = [
            php
            pkgs.php85Packages.composer
            pkgs.jq
            pkgs.awscli2
            pkgs.just

            # Databases
            databases.pkgs_postgresql
            databases.pkgs_valkey

            # Monitoring
            monitoring.pkgs_otel
            monitoring.pkgs_tempo
            monitoring.pkgs_loki
            monitoring.pkgs_prometheus
            monitoring.pkgs_grafana

            # Storage
            storage.pkgs_seaweedfs
          ];

          shellHook = ''
            export PROJ_ROOT="''${PWD}"
            export OTEL_PHP_AUTOLOAD_ENABLED=true

            # Create data directory
            mkdir -p "${config.dataDir}"

            # Export database configuration
            export POSTGRES_DATA_DIR="${config.postgresDataDir}"
            export VALKEY_DATA_DIR="${config.valkeyDataDir}"
            export POSTGRES_USER="${config.postgresUser}"
            export POSTGRES_PORT="${config.postgresPort}"
            export POSTGRES_DB="${config.postgresDb}"
            export VALKEY_PORT="${config.valkeyPort}"

            # Export storage configuration
            export SEAWEEDFS_DATA_DIR="${config.seaweedfsDataDir}"
            export S3_CREDENTIALS_FILE="${config.s3CredentialsFile}"
            export SEAWEEDFS_PORT="${config.seaweedfsPort}"
            export SEAWEEDFS_MASTER_PORT="${config.seaweedfsMasterPort}"
            export SEAWEEDFS_VOLUME_PORT="${config.seaweedfsVolumePort}"
            export SEAWEEDFS_FILER_PORT="${config.seaweedfsFilerPort}"
            export SEAWEEDFS_METRICS_PORT="${config.seaweedfsMetricsPort}"
            export SEAWEEDFS_ADMIN_DATA_DIR="${config.seaweedfsAdminDataDir}"
            export SEAWEEDFS_ADMIN_PORT="${config.seaweedfsAdminPort}"

            # Export monitoring configuration files
            export PROMETHEUS_CONFIG="${monitoring.configs.prometheus}"
            export OTEL_CONFIG="${monitoring.configs.otel}"
            export TEMPO_CONFIG="${monitoring.configs.tempo}"
            export LOKI_CONFIG="${monitoring.configs.loki}"
            export GRAFANA_INI="${monitoring.configs.grafana_ini}"
            export GRAFANA_DATASOURCES="${monitoring.configs.grafana_datasources}"
            export GRAFANA_HOME="${monitoring.pkgs_grafana}/share/grafana"

            # Export monitoring data directories and ports
            export OTEL_DATA_DIR="${config.otelDataDir}"
            export GRAFANA_DATA_DIR="${config.grafanaDataDir}"
            export TEMPO_DATA_DIR="${config.tempoDataDir}"
            export PROMETHEUS_DATA_DIR="${config.prometheusDataDir}"
            export LOKI_DATA_DIR="${config.lokiDataDir}"
            export PROMETHEUS_PORT="${config.prometheusPort}"
            export TEMPO_PORT="${config.tempoPort}"
            export LOKI_PORT="${config.lokiPort}"
            export OTEL_HEALTH_CHECK_PORT="${config.otelHealthCheckPort}"
            export GRAFANA_PORT="${config.grafanaPort}"

            echo "----------------------------------------------------------------"

            echo "collection_toolbox_backend dev environment"
            echo "----------------------------------------------------------------"
            echo "$(php --version | head -n 1)"
            echo "$(${databases.pkgs_postgresql}/bin/postgres --version)"
            echo "$(${databases.pkgs_valkey}/bin/valkey-server --version | head -n 1)"
            echo "$(${monitoring.pkgs_otel}/bin/otelcol-contrib --version)"
            echo "$(${monitoring.pkgs_tempo}/bin/tempo -version 2>&1 | head -n 1)"
            echo "$(${monitoring.pkgs_loki}/bin/loki --version 2>&1 | head -n 1)"
            echo "$(${monitoring.pkgs_prometheus}/bin/prometheus --version 2>&1 | head -n 1)"
            echo "$(${monitoring.pkgs_grafana}/bin/grafana -v | head -n 1)"
            echo "$(${storage.pkgs_seaweedfs}/bin/weed version 2>&1 | grep '^version' | head -n 1)"
            echo "aws-cli version: $(${pkgs.awscli2}/bin/aws --version | head -n 1)"
            echo "----------------------------------------------------------------"

            echo "Enabled PHP extensions:"
            php -m | grep -E "(pdo_pgsql|pgsql|redis|imagick|opentelemetry)"
            echo "----------------------------------------------------------------"

            # Export AWS credentials
            if [ -f "${config.s3CredentialsFile}" ]; then
              export AWS_ACCESS_KEY_ID=$(jq -r '.access_key' ${config.s3CredentialsFile})
              export AWS_SECRET_ACCESS_KEY=$(jq -r '.secret_key' ${config.s3CredentialsFile})
              export AWS_DEFAULT_REGION="us-east-1"
              export AWS_ENDPOINT_URL="http://127.0.0.1:8333"
              echo "aws-cli configured (http://127.0.0.1:8333)"
              echo "Data is stored in ./${config.dataDir}/"
            fi
            echo "----------------------------------------------------------------"
            just --list
          '';
        };

        packages.default = php;
      }
    );
}
