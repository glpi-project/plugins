# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is the **GLPI Plugin Directory** — a web application for browsing and managing plugins for the GLPI IT management system. It consists of:

- `api/` — PHP REST API built on Slim 2 + Eloquent ORM
- `frontend/` — AngularJS 1 SPA built with Grunt
- `specs/` — Modernization specs and test plans (functional, e2e)
- `misc/` — Background task runner and DB initialization scripts

The project is in **active modernization** (Step 1: adding test coverage is in progress). The stack is intentionally legacy (Slim 2, AngularJS 1) and will be upgraded in later steps.

## Commands

### API (PHP)

```bash
cd api
composer install                              # Install dependencies
vendor/bin/phpunit                            # Run all tests
vendor/bin/phpunit --testsuite Functional     # Functional tests (requires MySQL)
```

Functional tests require a MySQL database. Default credentials (overridable via env vars):
- DB: `glpi_plugins_functional_test`, user: `glpi`, pass: `glpi`, host: `localhost`
- Env vars: `TEST_DB_HOST`, `TEST_DB_NAME`, `TEST_DB_USER`, `TEST_DB_PASS`

### Frontend (JavaScript)

```bash
cd frontend
npm install && bower install   # Install dependencies
grunt build                    # Build production output to dist/
grunt serve                    # Dev server at http://localhost:9000
grunt test                     # Run Karma/Jasmine tests
```

### Docker

```bash
docker-compose up -d                                    # Start dev environment
docker exec -it plugins.glpi-project.org bash          # Enter container
# Inside container: Apache on :8080, Node dev server on :9000
```

### Background Tasks

```bash
php misc/run_tasks.php                        # Run all tasks (plugin updates, token cleanup)
php misc/run_tasks.php -k genericobject -t update   # Update specific plugin by key
php misc/run_tasks.php -i 44 -t update        # Update plugin by DB id
```

## Architecture

### API (Slim 2)

Entry point: `api/index.php` — initializes Illuminate DB capsule, Slim app, OAuth2 resource server, then `require`s all files from `src/endpoints/`.

```
api/src/
├── core/          # Tool.php (request/response helpers), DB.php, Mailer.php, PaginatedCollection.php, ValidableXMLPluginDescription.php
├── endpoints/     # One file per resource (Plugin.php, User.php, Author.php, OAuth.php, Tags.php, …)
├── models/        # Eloquent ORM models (Plugin, User, Author, PluginVersion, OAuth tokens, …)
├── exceptions/    # ErrorResponse base + subclasses (InvalidField, ResourceNotFound, …)
└── oauthserver/   # OAuthHelper.php — league/oauth2-server v4.1 storage & factory
```

**Key patterns:**
- Endpoint files are procedural PHP modules, not classes — they register Slim routes directly.
- Pagination uses HTTP headers (`x-range`, `x-lang`) rather than query params.
- OAuth2 supports `password`, `refresh_token`, and `client_credentials` grants.
- Config is loaded from `api/config.php` (copy from `api/config.example.php`).

### Tests

**Functional tests** (`api/tests/Functional/`) — spin up PHP built-in server as subprocess, send real HTTP requests via Guzzle. Each test class reloads seeds (`tests/Functional/seeds.sql`) and wraps tests in a rolled-back transaction. Schema is initialized once per suite from `tests/Functional/schema.sql`.

**Frontend tests** (`frontend/test/spec/`) — Karma + Jasmine, cover controllers, services, directives, filters.

### Frontend (AngularJS 1)

SPA routed via `ng-route`. API endpoint configured in `frontend/app/scripts/conf.js` (copy from `conf.example.js`). Build output goes to `frontend/dist/`.

## CI/CD

GitHub Actions (`.github/workflows/tests_phpunit.yml`):
- **Functional**: PHP 7.4 + MySQL 8.0
- Triggers on push/PR to `master`

## Spec Files

Detailed test specifications live in `specs/testing/`:
- `functional.md` — what functional tests should cover
- `e2e.md` — E2E test plan

`specs/api/endpoints.md` is the full REST API reference including auth scopes, request/response shapes, and pagination behavior.
