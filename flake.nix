{
  description = "PHP 8.5 development environment with PostgreSQL 18 and Valkey";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
    flake-utils.url = "github:numtide/flake-utils";
  };

  outputs =
    {
      self,
      nixpkgs,
      flake-utils,
    }:
    flake-utils.lib.eachDefaultSystem (
      system:
      let
        pkgs = nixpkgs.legacyPackages.${system};
        pkgs_postgresql = pkgs.postgresql_18;
        pkgs_valkey = pkgs.valkey;

        php = pkgs.php85.buildEnv {
          extensions = (
            { enabled, all }:
            enabled
            ++ (with all; [
              pdo_pgsql
              pgsql
              redis
            ])
          );
          extraConfig = ''
            memory_limit = 256M
            upload_max_filesize = 20M
            post_max_size = 20M
          '';
        };

        dataDir = ".nix-data";
        postgresDataDir = "${dataDir}/postgres";
        valkeyDataDir = "${dataDir}/valkey";

        postgresPort = "5432";
        postgresUser = "postgres";
        postgresDb = "collection_toolbox_backend";

        valkeyPort = "6379";

        startServices = pkgs.writeShellScriptBin "start-services" ''
          mkdir -p ${postgresDataDir} ${valkeyDataDir}

          PGDATA_ABS="$(pwd)/${postgresDataDir}"
          PGLOG="$PGDATA_ABS/logfile"

          if [ ! -d "${postgresDataDir}/base" ]; then
            echo "Initializing PostgreSQL database..."
            ${pkgs_postgresql}/bin/initdb -D "$PGDATA_ABS" -U ${postgresUser} --no-locale --encoding=UTF8 -A trust
            echo "unix_socket_directories = '$PGDATA_ABS'" >> "$PGDATA_ABS/postgresql.conf"
          fi

          # Check if our specific PostgreSQL instance is running
          if [ -f "$PGDATA_ABS/postmaster.pid" ] && ${pkgs_postgresql}/bin/pg_ctl -D "$PGDATA_ABS" status > /dev/null 2>&1; then
            echo "PostgreSQL is already running"
          else
            # Clean up stale pid file if it exists
            [ -f "$PGDATA_ABS/postmaster.pid" ] && rm -f "$PGDATA_ABS/postmaster.pid"

            # Start PostgreSQL
            ${pkgs_postgresql}/bin/pg_ctl -D "$PGDATA_ABS" -l "$PGLOG" -o "-p ${postgresPort} -k $PGDATA_ABS" start > /dev/null

            until ${pkgs_postgresql}/bin/pg_isready -p ${postgresPort} -h localhost > /dev/null 2>&1; do
              sleep 0.1
            done
          fi

          ${pkgs_postgresql}/bin/psql -p ${postgresPort} -h localhost -U ${postgresUser} -lqt | cut -d \| -f 1 | grep -qw ${postgresDb} || \
            ${pkgs_postgresql}/bin/createdb -p ${postgresPort} -h localhost -U ${postgresUser} ${postgresDb}

          if ${pkgs_valkey}/bin/valkey-cli -p ${valkeyPort} ping > /dev/null 2>&1; then
            echo "Valkey is already running"
          else
            ${pkgs_valkey}/bin/valkey-server \
              --port ${valkeyPort} \
              --dir $(pwd)/${valkeyDataDir} \
              --appendonly yes \
              --daemonize yes \
              --pidfile $(pwd)/${valkeyDataDir}/valkey.pid \
              > /dev/null 2>&1

            until ${pkgs_valkey}/bin/valkey-cli -p ${valkeyPort} ping > /dev/null 2>&1; do
              sleep 0.1
            done
          fi

          echo "postgresql://${postgresUser}@localhost:${postgresPort}/${postgresDb}"
          echo "valkey://localhost:${valkeyPort}"
        '';

        stopServices = pkgs.writeShellScriptBin "stop-services" ''
          echo "Stopping services..."

          PGDATA_ABS="$(pwd)/${postgresDataDir}"

          if [ -f "$PGDATA_ABS/postmaster.pid" ]; then
            ${pkgs_postgresql}/bin/pg_ctl -D "$PGDATA_ABS" stop -m fast > /dev/null 2>&1
          fi

          if [ -f "$(pwd)/${valkeyDataDir}/valkey.pid" ]; then
            ${pkgs_valkey}/bin/valkey-cli -p ${valkeyPort} shutdown > /dev/null 2>&1 || true
          fi

          echo "Services stopped"
        '';

      in
      {
        devShells.default = pkgs.mkShell {
          buildInputs = [
            php
            pkgs.php85Packages.composer
            pkgs_postgresql
            pkgs_valkey
            startServices
            stopServices
          ];

          shellHook = ''
            echo "PHP 8.5 development environment with PostgreSQL 18 and Valkey 9"
            echo ""
            echo "PHP version: $(php --version | head -n 1)"
            echo "PostgreSQL version: $(${pkgs_postgresql}/bin/postgres --version)"
            echo "Valkey version: $(${pkgs_valkey}/bin/valkey-server --version | head -n 1)"
            echo ""
            echo "Enabled PHP extensions:"
            php -m | grep -E "(pdo_pgsql|pgsql|redis)"
            echo ""

            # Start services automatically
            start-services

            # Set up trap to stop services on exit
            trap stop-services EXIT

            echo ""
            echo "Data is stored in ./${dataDir}/"
            echo "Services will stop automatically when you exit this shell."
          '';
        };

        packages.default = php;
      }
    );
}
