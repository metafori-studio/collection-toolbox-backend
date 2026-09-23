# Collection Toolbox Backend

noot noot

Monorepo of Laravel applications and shared modules for the Metafori Collection Toolbox — museum collection management backends with Filament admin panels and REST APIs.

## Repository structure

| Path | Description |
|------|-------------|
| `apps/` | Deployable Laravel applications (each has its own `.env`, `composer.json`, and migrations) |
| `app-modules/` | Shared PHP packages consumed by the apps (`core`, domain modules, monitoring, opensearch) |

### Applications

| App | Package | Description |
|-----|---------|-------------|
| [`apps/etno`](apps/etno) | `metafori/etnoskop` | Ethnographic collections — items, research collections, media, search API |
| [`apps/archeo`](apps/archeo) | `metafori/archeomap` | Archaeological collections |
| [`apps/art`](apps/art) | `metafori/art` | Lightweight admin shell (shared user management) |

### Shared modules

| Module | Purpose |
|--------|---------|
| `core` | Users, roles, permissions, localities, shared Filament resources |
| `etno` | Etno domain logic, Filament resources, API |
| `archeo` | Archeo domain logic, Filament resources |
| `monitoring` | Prometheus metrics |
| `opensearch` | OpenSearch integration |

## Prerequisites

- [Nix](https://nixos.org/download/) with flakes enabled
- SSH access to `git@github.com:metafori-studio/infra.git` (used by `flake.nix` for the dev shell)

## Local setup

### 1. Enter the development shell

```bash
nix develop
```

This provides PHP 8.5, PostgreSQL, Valkey (Redis), S3-compatible storage, and monitoring tooling.

**Services are not started automatically** — you need to start them manually after entering the shell.

### 2. Start infrastructure services

From the repository root (inside the Nix shell):

```bash
just start-postgres    # minimum for local dev (postgres + valkey)
```

For basic app development with the default `.env.example` settings, this is all you need.

Optional — start the full local stack (S3 storage, Prometheus, Grafana, …):

```bash
just all
```

Stop services with `just stop-postgres` or `just die`.

### 3. Set up an application

Pick the app you want to run and work from its directory:

```bash
cd apps/etno   # or apps/archeo, apps/art

composer install
composer setup   # copies .env, generates APP_KEY, runs migrations & seeders
composer dev     # php artisan serve + queue worker + log tail (pail)
```

`composer dev` starts the app at **http://127.0.0.1:8000**.

### 4. Create an admin user

```bash
php artisan core:make:user
```

Prompts for name, email, password, and roles.

### 5. Optional — seed Slovak locality data

Useful for local development with geographic / locality fields:

```bash
php artisan db:seed --class="Metafori\Core\Database\Seeders\Locality\SlovakiaSeeder" --force
```

### 6. Locale

For Slovak locale, set in `.env`:

```env
APP_LOCALE=sk
```

## URLs (local)

Paths differ per application. Replace the host/port if you changed them.

| App | Filament admin | API docs |
|-----|----------------|----------|
| **etno** | http://127.0.0.1:8000/etno/ | http://127.0.0.1:8000/docs/api |
| **archeo** | http://127.0.0.1:8000/archeo/ | http://127.0.0.1:8000/docs/api |
| **art** | http://127.0.0.1:8000/ | — |

API documentation is powered by [Scramble](https://scramble.dedoc.co/) and is available on apps that expose an API (etno, archeo).

## Default database

The Nix dev shell configures PostgreSQL with:

- **Database:** `collection_toolbox_backend`
- **Host:** `127.0.0.1`
- **Port:** `5432`
- **User:** `postgres`

These match the values in each app's `.env.example`.

## Useful commands

```bash
# From repo root (Nix shell)
just --list              # all infrastructure commands

# From an app directory
composer test            # run tests
php artisan migrate      # run pending migrations
php artisan db:seed      # run DatabaseSeeder
```
