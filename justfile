set shell := ["bash", "-c"]

export PROJ_ROOT := env_var_or_default("PROJ_ROOT", `pwd`)

setup:
    @composer run setup

run:
    @composer run dev

start-storage:
    @start-storage

stop-storage:
    @stop-storage

start-databases:
    @start-databases

stop-databases:
    @stop-databases

start-monitoring:
    @start-monitoring

stop-monitoring:
    @stop-monitoring

flake-update:
    nix flake update --extra-experimental-features "nix-command flakes"

nix-gc:
    nix-store --gc
    nix-collect-garbage -d

all:
    @start-all

die:
    @stop-all
