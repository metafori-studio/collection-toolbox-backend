set shell := ["bash", "-c"]

export PROJ_ROOT := env_var_or_default("PROJ_ROOT", `pwd`)

start:
    @nix develop

setup:
    @composer run setup

run:
    @composer run dev

start-storage:
    @./nix/scripts/start/storage

stop-storage:
    @./nix/scripts/stop/storage

start-databases:
    @./nix/scripts/start/databases

stop-databases:
    @./nix/scripts/stop/databases

start-monitoring:
    @./nix/scripts/start/monitoring

stop-monitoring:
    @./nix/scripts/stop/monitoring

all: start-storage start-databases start-monitoring
die: stop-monitoring stop-databases stop-storage
