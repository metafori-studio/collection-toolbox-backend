set shell := ["bash", "-c"]

export PROJ_ROOT := env_var_or_default("PROJ_ROOT", `pwd`)

default:
    @just --list

start-storage:
    @start-storage

stop-storage:
    @stop-storage

start-postgres:
    @start-postgres

stop-postgres:
    @stop-postgres

start-valkey:
    @start-valkey

stop-valkey:
    @stop-valkey

start-opensearch:
    @start-opensearch

stop-opensearch:
    @stop-opensearch

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
