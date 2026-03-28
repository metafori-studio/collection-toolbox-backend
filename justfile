set shell := ["bash", "-c"]

export PROJ_ROOT := env_var_or_default("PROJ_ROOT", `pwd`)

setup:
    @composer run setup

run:
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

flake-update:
    nix flake update --extra-experimental-features "nix-command flakes"

nix-gc:
    nix-store --gc
    nix-collect-garbage -d

all: start-storage start-databases start-monitoring
die: stop-monitoring stop-databases stop-storage
