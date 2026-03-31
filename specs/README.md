# GLPI Plugin Directory — Modernization Specs

This folder contains specifications for modernizing the GLPI Plugin Directory.
The project consists of a **Slim 2 PHP REST API** and an **AngularJS 1 frontend**.

## Modernization Roadmap

| Step | Status | Description |
|------|--------|-------------|
| 1    | 🔄 In Progress | Add unit, functional, and E2E tests |
| 2    | ⏳ Planned | Upgrade backend (PHP 8+, Slim 4, modern OAuth) |
| 3    | ⏳ Planned | Migrate frontend to a modern framework (Vue 3 / React) |
| 4    | ⏳ Planned | CI/CD pipeline, containerization |

## Repository Layout

```
plugins/
├── api/                  # Slim 2 PHP backend
│   ├── src/
│   │   ├── core/         # Tool, DB, Mailer, BackgroundTasks, OAuthClient
│   │   ├── endpoints/    # Route handlers (one file per resource)
│   │   ├── models/       # Eloquent ORM models
│   │   ├── exceptions/   # Custom exception hierarchy
│   │   └── oauthserver/  # OAuth2 server implementation
│   ├── mailtemplates/    # Twig email templates
│   └── misc/             # Background task runner scripts
├── frontend/             # AngularJS 1 SPA
│   ├── app/
│   │   ├── scripts/      # Controllers, services, directives, filters
│   │   └── views/        # HTML templates
│   └── test/             # Existing Karma/Jasmine specs
└── specs/                # ← You are here
    ├── api/
    │   └── endpoints.md  # Full API endpoint reference
    └── testing/
        ├── unit.md       # Unit test specifications
        ├── functional.md # Functional/integration test specifications
        └── e2e.md        # End-to-end test specifications
```

## Specs Index

- [API Endpoint Reference](api/endpoints.md)
- [Unit Test Specifications](testing/unit.md)
- [Functional Test Specifications](testing/functional.md)
- [E2E Test Specifications](testing/e2e.md)
