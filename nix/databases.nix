{
  pkgs,
  config,
}:
let
  inherit (config)
    postgresDataDir
    valkeyDataDir
    postgresPort
    postgresUser
    postgresDb
    valkeyPort
    ;

  pkgs_postgresql = pkgs.postgresql_18;
  pkgs_valkey = pkgs.valkey;

  startDatabases = pkgs.writeShellScriptBin "start-databases" ''
    mkdir -p ${postgresDataDir} ${valkeyDataDir}

    PGDATA_ABS="$(pwd)/${postgresDataDir}"
    PGLOG="$PGDATA_ABS/logfile"

    if [ ! -d "${postgresDataDir}/base" ]; then
      echo "Initializing PostgreSQL database..."
      ${pkgs_postgresql}/bin/initdb -D "$PGDATA_ABS" -U ${postgresUser} --no-locale --encoding=UTF8 -A trust
      echo "unix_socket_directories = '$PGDATA_ABS'" >> "$PGDATA_ABS/postgresql.conf"
    fi

    # Start PostgreSQL
    if [ -f "$PGDATA_ABS/postmaster.pid" ] && ${pkgs_postgresql}/bin/pg_ctl -D "$PGDATA_ABS" status > /dev/null 2>&1; then
      echo "PostgreSQL is already running"
    else
      [ -f "$PGDATA_ABS/postmaster.pid" ] && rm -f "$PGDATA_ABS/postmaster.pid"
      ${pkgs_postgresql}/bin/pg_ctl -D "$PGDATA_ABS" -l "$PGLOG" -o "-p ${postgresPort} -k $PGDATA_ABS" start > /dev/null

      echo -n "Waiting for PostgreSQL to be ready..."
      count=0
      until ${pkgs_postgresql}/bin/pg_isready -p ${postgresPort} -h localhost > /dev/null 2>&1; do
        sleep 1
        count=$((count + 1))
        if [ $count -ge 30 ]; then echo " TIMEOUT"; break; fi
      done
      echo " READY"
    fi

    # Create postgresql databases
    ${pkgs_postgresql}/bin/psql -p ${postgresPort} -h localhost -U ${postgresUser} -lqt | cut -d \| -f 1 | grep -qw ${postgresDb} || \
      ${pkgs_postgresql}/bin/createdb -p ${postgresPort} -h localhost -U ${postgresUser} ${postgresDb}

    # Start Valkey
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

      echo -n "Waiting for Valkey to be ready..."
      count=0
      until ${pkgs_valkey}/bin/valkey-cli -p ${valkeyPort} ping > /dev/null 2>&1; do
        sleep 1
        count=$((count + 1))
        if [ $count -ge 30 ]; then echo " TIMEOUT"; break; fi
      done
      echo " READY"
    fi

    echo "Database connections:"
    echo "  PostgreSQL: postgresql://${postgresUser}@localhost:${postgresPort}/${postgresDb}"
    echo "  Valkey: valkey://localhost:${valkeyPort}"
  '';

  stopDatabases = pkgs.writeShellScriptBin "stop-databases" ''
    PGDATA_ABS="$(pwd)/${postgresDataDir}"
    if [ -f "$PGDATA_ABS/postmaster.pid" ]; then
      echo "Stopping PostgreSQL..."
      ${pkgs_postgresql}/bin/pg_ctl -D "$PGDATA_ABS" stop -m fast > /dev/null 2>&1
    fi

    if [ -f "$(pwd)/${valkeyDataDir}/valkey.pid" ]; then
      echo "Stopping Valkey..."
      ${pkgs_valkey}/bin/valkey-cli -p ${valkeyPort} shutdown > /dev/null 2>&1 || true
    fi
  '';
in
{
  start = startDatabases;
  stop = stopDatabases;
  inherit pkgs_postgresql pkgs_valkey;
}
