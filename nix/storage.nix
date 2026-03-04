{
  pkgs,
  config,
}: let
  inherit
    (config)
    seaweedfsDataDir
    seaweedfsPort
    seaweedfsMasterPort
    seaweedfsVolumePort
    seaweedfsFilerPort
    seaweedfsMetricsPort
    seaweedfsAdminPort
    seaweedfsAdminDataDir
    s3CredentialsFile
    ;
  pkgs_seaweedfs = pkgs.seaweedfs;
  pkgs_jq = pkgs.jq;

  startStorage = pkgs.writeShellScriptBin "start-storage" ''
    PROJ_ROOT="''${PROJECT_ROOT:-$(pwd)}"
    mkdir -p "$PROJ_ROOT/${seaweedfsDataDir}"

    SEAWEEDFS_DATA_ABS="$PROJ_ROOT/${seaweedfsDataDir}"
    S3_CONFIG="$SEAWEEDFS_DATA_ABS/s3.json"
    CREDENTIALS_FILE="$PROJ_ROOT/${s3CredentialsFile}"
    PIDFILE="$SEAWEEDFS_DATA_ABS/seaweedfs.pid"

    if [ ! -f "$CREDENTIALS_FILE" ]; then
      echo "Generating S3 credentials..."
      ACCESS_KEY=$(cat /dev/urandom | tr -dc 'A-Z0-9' | fold -w 20 | head -n 1)
      SECRET_KEY=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 40 | head -n 1)

      "${pkgs_jq}/bin/jq" -n \
        --arg key "$ACCESS_KEY" \
        --arg secret "$SECRET_KEY" \
        '{access_key: $key, secret_key: $secret}' > "$CREDENTIALS_FILE"
    fi

    ACCESS_KEY=$("${pkgs_jq}/bin/jq" -r '.access_key' "$CREDENTIALS_FILE")
    SECRET_KEY=$("${pkgs_jq}/bin/jq" -r '.secret_key' "$CREDENTIALS_FILE")

    "${pkgs_jq}/bin/jq" -n \
      --arg key "$ACCESS_KEY" \
      --arg secret "$SECRET_KEY" \
      '{identities: [{name: "collection-toolbox-assets", credentials: [{accessKey: $key, secretKey: $secret}], actions: ["Admin", "Read", "Write"]}]}' > "$S3_CONFIG"

    if [ -f "$PIDFILE" ] && kill -0 "$(cat "$PIDFILE")" 2>/dev/null; then
      echo "SeaweedFS is already running"
    else
      [ -f "$PIDFILE" ] && rm -f "$PIDFILE"
      echo "Starting SeaweedFS..."
      "${pkgs_seaweedfs}/bin/weed" server \
        -dir="$SEAWEEDFS_DATA_ABS" \
        -s3 \
        -s3.port="${toString seaweedfsPort}" \
        -s3.config="$S3_CONFIG" \
        -master.port="${toString seaweedfsMasterPort}" \
        -volume.port="${toString seaweedfsVolumePort}" \
        -filer.port="${toString seaweedfsFilerPort}" \
        -metricsPort="${toString seaweedfsMetricsPort}" \
        -metricsIp=127.0.0.1 \
        -ip=127.0.0.1 \
        > "$SEAWEEDFS_DATA_ABS/seaweedfs.log" 2>&1 &
      echo $! > "$PIDFILE"

      echo -n "Waiting for SeaweedFS to be ready..."
      count=0
      until curl -s -f "http://127.0.0.1:${toString seaweedfsMasterPort}/cluster/status" > /dev/null 2>&1; do
        sleep 1
        count=$((count + 1))
        if [ $count -ge 30 ]; then
          echo " TIMEOUT"
          echo "SeaweedFS failed to start. Check logs at $SEAWEEDFS_DATA_ABS/seaweedfs.log"
          cat "$SEAWEEDFS_DATA_ABS/seaweedfs.log"
          break
        fi
      done

      if [ $count -lt 30 ]; then
        until curl -s "http://127.0.0.1:${toString seaweedfsPort}" > /dev/null 2>&1; do
           sleep 1
        done
        echo " READY"
      fi
    fi

    count=0
    until curl -s -f -X POST "http://127.0.0.1:${toString seaweedfsFilerPort}/buckets/collection-toolbox-assets/" > /dev/null 2>&1; do
      sleep 1
      count=$((count + 1))
      if [ $count -ge 10 ]; then break; fi
    done

    # SeaweedFS Admin
    ADMIN_PIDFILE="$PROJ_ROOT/${seaweedfsAdminDataDir}/admin.pid"
    mkdir -p "$PROJ_ROOT/${seaweedfsAdminDataDir}"
    if [ ! -f "$ADMIN_PIDFILE" ] || ! kill -0 "$(cat "$ADMIN_PIDFILE")" 2>/dev/null; then
      echo "Starting SeaweedFS Admin Interface..."
      "${pkgs_seaweedfs}/bin/weed" admin \
        -master="localhost:${toString seaweedfsMasterPort}" \
        -port="${toString seaweedfsAdminPort}" \
        -dataDir="$PROJ_ROOT/${seaweedfsAdminDataDir}" \
        > "$PROJ_ROOT/${seaweedfsAdminDataDir}/admin.log" 2>&1 &
      echo $! > "$ADMIN_PIDFILE"

      echo -n "Waiting for SeaweedFS Admin to be ready..."
      count=0
      until curl -s "http://127.0.0.1:${toString seaweedfsAdminPort}/api/health" > /dev/null 2>&1 || [ $count -ge 10 ]; do
        sleep 1
        count=$((count + 1))
      done
      echo " READY"
    fi

    echo "  SeaweedFS Master: http://127.0.0.1:${toString seaweedfsMasterPort}"
    echo "  SeaweedFS Filer: http://127.0.0.1:${toString seaweedfsFilerPort}"
    echo "  SeaweedFS S3: http://127.0.0.1:${toString seaweedfsPort}"
    echo "  SeaweedFS Admin: http://127.0.0.1:${toString seaweedfsAdminPort}"
    echo "  Bucket: collection-toolbox-assets"
    echo "  Credentials stored in nix/s3_credentials.json"
  '';

  stopStorage = pkgs.writeShellScriptBin "stop-storage" ''
    PROJ_ROOT="''${PROJECT_ROOT:-$(pwd)}"

    stop_pid() {
      name=$1
      pidfile=$2
      if [ -f "$pidfile" ]; then
        PID=$(cat "$pidfile")
        if kill -0 "$PID" 2>/dev/null; then
          echo "Stopping $name (PID: $PID)..."
          kill "$PID" 2>/dev/null || true
          count=0
          while kill -0 "$PID" 2>/dev/null; do
            sleep 0.5
            count=$((count + 1))
            if [ $count -ge 10 ]; then
              echo "Forcefully killing $name..."
              kill -9 "$PID" 2>/dev/null || true
              break
            fi
          done
        fi
        rm -f "$pidfile"
      fi
    }

    stop_pid "SeaweedFS" "$PROJ_ROOT/${seaweedfsDataDir}/seaweedfs.pid"
    stop_pid "SeaweedFS Admin" "$PROJ_ROOT/${seaweedfsAdminDataDir}/admin.pid"
  '';
in {
  start = startStorage;
  stop = stopStorage;
  inherit pkgs_seaweedfs;
}
