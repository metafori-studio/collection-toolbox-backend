set shell := ["bash", "-c"]

export PROJ_ROOT := env_var_or_default("PROJ_ROOT", `pwd`)

start:
    @nix develop

setup:
    @composer run setup

dev:
    @composer run dev

start-storage:
    @$INFRA_SCRIPTS/start/storage

stop-storage:
    @$INFRA_SCRIPTS/stop/storage

start-databases:
    @$INFRA_SCRIPTS/start/databases

stop-databases:
    @$INFRA_SCRIPTS/stop/databases

start-monitoring:
    @$INFRA_SCRIPTS/start/monitoring

stop-monitoring:
    @$INFRA_SCRIPTS/stop/monitoring

all: start-storage start-databases start-monitoring
die: stop-monitoring stop-databases stop-storage

run:
    @npm run build
    @npx concurrently --kill-others -c "#93c5fd,#c4b5fd" \
        "php artisan octane:start --server=frankenphp --host=127.0.0.1 --port=8000" \
        "php artisan queue:work --tries=3 --timeout=0" \
        --names=server,worker
