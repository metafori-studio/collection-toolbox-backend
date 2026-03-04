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
    PROJ_ROOT="''${PROJECT_ROOT:-$(pwd)}"
    mkdir -p "$PROJ_ROOT/${postgresDataDir}" "$PROJ_ROOT/${valkeyDataDir}"

    PGDATA_ABS="$PROJ_ROOT/${postgresDataDir}"
    PGLOG="$PGDATA_ABS/logfile"

    if [ ! -d "$PGDATA_ABS/base" ]; then
      echo "Initializing PostgreSQL database..."
      "${pkgs_postgresql}/bin/initdb" -D "$PGDATA_ABS" -U "${postgresUser}" --no-locale --encoding=UTF8 -A trust
      {
        echo "unix_socket_directories = '$PGDATA_ABS'"
        echo "listen_addresses = 'localhost'"
      } >> "$PGDATA_ABS/postgresql.conf"
    fi

    # PostgreSQL
    if [ -f "$PGDATA_ABS/postmaster.pid" ] && "${pkgs_postgresql}/bin/pg_ctl" -D "$PGDATA_ABS" status > /dev/null 2>&1; then
      echo "PostgreSQL is already running"
    else
      [ -f "$PGDATA_ABS/postmaster.pid" ] && rm -f "$PGDATA_ABS/postmaster.pid"
      "${pkgs_postgresql}/bin/pg_ctl" -D "$PGDATA_ABS" -l "$PGLOG" -o "-p ${toString postgresPort} -k $PGDATA_ABS" start > /dev/null

      echo -n "Waiting for PostgreSQL to be ready..."
      count=0
      until "${pkgs_postgresql}/bin/pg_isready" -p "${toString postgresPort}" -h "$PGDATA_ABS" > /dev/null 2>&1; do
        sleep 1
        count=$((count + 1))
        if [ $count -ge 30 ]; then
          echo " TIMEOUT"
          exit 1
        fi
      done
      echo " READY"
    fi

    "${pkgs_postgresql}/bin/psql" -p "${toString postgresPort}" -h "$PGDATA_ABS" -U "${postgresUser}" -lqt | cut -d \| -f 1 | grep -qw "${postgresDb}" || \
      "${pkgs_postgresql}/bin/createdb" -p "${toString postgresPort}" -h "$PGDATA_ABS" -U "${postgresUser}" "${postgresDb}"

    # Valkey
    if "${pkgs_valkey}/bin/valkey-cli" -p "${toString valkeyPort}" ping > /dev/null 2>&1; then
      echo "Valkey is already running"
    else
      "${pkgs_valkey}/bin/valkey-server" \
        --port "${toString valkeyPort}" \
        --dir "$PROJ_ROOT/${valkeyDataDir}" \
        --appendonly yes \
        --daemonize yes \
        --pidfile "$PROJ_ROOT/${valkeyDataDir}/valkey.pid" \
        > "$PROJ_ROOT/${valkeyDataDir}/valkey.log" 2>&1

      echo -n "Waiting for Valkey to be ready..."
      count=0
      until "${pkgs_valkey}/bin/valkey-cli" -p "${toString valkeyPort}" ping > /dev/null 2>&1; do
        sleep 1
        count=$((count + 1))
        if [ $count -ge 30 ]; then echo " TIMEOUT"; break; fi
      done
      echo " READY"
    fi

    echo "  PostgreSQL: postgresql://${postgresUser}@localhost:${toString postgresPort}/${postgresDb}"
    echo "  Valkey: valkey://localhost:${toString valkeyPort}"
  '';

  stopDatabases = pkgs.writeShellScriptBin "stop-databases" ''
    PROJ_ROOT="''${PROJECT_ROOT:-$(pwd)}"
    PGDATA_ABS="$PROJ_ROOT/${postgresDataDir}"
    if [ -f "$PGDATA_ABS/postmaster.pid" ]; then
      echo "Stopping PostgreSQL..."
      "${pkgs_postgresql}/bin/pg_ctl" -D "$PGDATA_ABS" stop -m fast > /dev/null 2>&1
    fi

    VALKEY_PIDFILE="$PROJ_ROOT/${valkeyDataDir}/valkey.pid"
    if [ -f "$VALKEY_PIDFILE" ]; then
      PID=$(cat "$VALKEY_PIDFILE")
      if kill -0 "$PID" 2>/dev/null; then
        echo "Stopping Valkey (PID: $PID)..."
        "${pkgs_valkey}/bin/valkey-cli" -p "${toString valkeyPort}" shutdown > /dev/null 2>&1 || true
        count=0
        while kill -0 "$PID" 2>/dev/null; do
          sleep 0.5
          count=$((count + 1))
          if [ $count -ge 10 ]; then
            echo "Forcefully killing Valkey..."
            kill -9 "$PID" 2>/dev/null || true
            break
          fi
        done
      fi
      rm -f "$VALKEY_PIDFILE"
    fi
  '';
in
{
  start = startDatabases;
  stop = stopDatabases;
  inherit pkgs_postgresql pkgs_valkey;
}
