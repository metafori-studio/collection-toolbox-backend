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

        config = import ./nix/config.nix;
        php = import ./nix/php.nix { inherit pkgs; };
        databases = import ./nix/databases.nix { inherit pkgs config; };
        monitoring = import ./nix/monitoring.nix { inherit pkgs config; };
        storage = import ./nix/storage.nix { inherit pkgs config; };

        startAll = pkgs.writeShellScriptBin "start-all" ''
          ${storage.start}/bin/start-storage
          ${databases.start}/bin/start-databases
          ${monitoring.start}/bin/start-monitoring
        '';

        stopAll = pkgs.writeShellScriptBin "stop-all" ''
          ${monitoring.stop}/bin/stop-monitoring
          ${databases.stop}/bin/stop-databases
          ${storage.stop}/bin/stop-storage
        '';
      in
      {
        devShells.default = pkgs.mkShell {
          buildInputs = [
            php
            pkgs.php85Packages.composer
            pkgs.jq
            pkgs.awscli2

            startAll
            stopAll

            # Databases
            databases.pkgs_postgresql
            databases.pkgs_valkey
            databases.start
            databases.stop

            # Monitoring
            monitoring.pkgs_otel
            monitoring.pkgs_tempo
            monitoring.pkgs_loki
            monitoring.pkgs_prometheus
            monitoring.pkgs_grafana
            monitoring.start
            monitoring.stop

            # Storage
            storage.pkgs_seaweedfs
            storage.start
            storage.stop
          ];

          shellHook = ''
            export PROJECT_ROOT="''${PWD}"
            export OTEL_SERVICE_NAME="collection_toolbox_backend"
            export OTEL_EXPORTER_OTLP_ENDPOINT="http://127.0.0.1:4318"
            export OTEL_EXPORTER_OTLP_PROTOCOL="http/protobuf"
            export OTEL_TRACES_SAMPLER="always_on"
            export OTEL_LOGS_EXPORTER="otlp"
            export OTEL_METRICS_EXPORTER="otlp"
            export OTEL_PHP_AUTOLOAD_ENABLED=true

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

            # Start services
            ${storage.start}/bin/start-storage
            ${databases.start}/bin/start-databases
            ${monitoring.start}/bin/start-monitoring

            # Export AWS credentials
            if [ -f "${config.s3CredentialsFile}" ]; then
              export AWS_ACCESS_KEY_ID=$(jq -r '.access_key' ${config.s3CredentialsFile})
              export AWS_SECRET_ACCESS_KEY=$(jq -r '.secret_key' ${config.s3CredentialsFile})
              export AWS_DEFAULT_REGION="us-east-1"
              export AWS_ENDPOINT_URL="http://127.0.0.1:8333"
              echo "aws-cli configured for SeaweedFS (http://127.0.0.1:8333)"
            fi

            # Set up trap to stop services on exit
            trap "${stopAll}/bin/stop-all" EXIT

            echo "----------------------------------------------------------------"
            echo "Data is stored in ./${config.dataDir}/"
            echo "Services will stop automatically when you exit this shell."
            echo "----------------------------------------------------------------"
          '';
        };

        packages.default = php;
      }
    );
}
