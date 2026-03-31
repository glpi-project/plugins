# End-to-End Test Specifications

E2E tests exercise the **full stack** through a real browser. They are the slowest
layer but give the highest confidence before a release.

## Stack

| Tool | Purpose |
|------|---------|
| [Playwright](https://playwright.dev/) | Browser automation |
| Docker Compose | Full stack (API + DB + frontend) |

**Suggested location:** `frontend/e2e/`

---

## Environment Setup

```yaml
# docker-compose.e2e.yml
services:
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: glpi_plugins_e2e
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 5s
      timeout: 5s
      retries: 10

  api:
    # Reuses the same Apache/PHP 7.4 image as the functional test stack
    build:
      context: .
      dockerfile: .docker/test/api/Dockerfile
    volumes:
      - ./api:/var/www/api
    environment:
      APP_CONFIG_FILE: /var/www/api/config.e2e.php
      TEST_DB_HOST: mysql
      TEST_DB_NAME: glpi_plugins_e2e
      TEST_DB_USER: root
      TEST_DB_PASS: root
    depends_on:
      mysql:
        condition: service_healthy

  frontend:
    # Builds the AngularJS SPA (grunt build) and serves dist/ statically
    build:
      context: .
      dockerfile: .docker/e2e/frontend/Dockerfile
    ports:
      - "4200:80"
    depends_on:
      - api
```

`config.e2e.php` mirrors `config.php` but points to the `glpi_plugins_e2e` database.

Run: `docker compose -f docker-compose.e2e.yml up -d`

---

## Frontend E2E Test Suites (Playwright)

### Selector strategy

Target **content and semantics**, not UI framework internals.
The frontend is currently AngularJS 1 but will be replaced; CSS classes,
`ng-*` attributes, and component structure will change. Tests must not.

Preferred selectors, in order:

1. **Visible text / labels** — `getByText('Sign in')`, `getByLabel('Password')`
2. **ARIA roles** — `getByRole('button', { name: 'Watch' })`, `getByRole('navigation')`
3. **Semantic HTML** — `<h1>`, `<nav>`, `<main>`, `<table>`
4. **`data-testid` attributes** — add sparingly, only when 1–3 are insufficient

Avoid: CSS class selectors (`.plugin-card`, `.ng-binding`), XPath, positional
selectors (`:nth-child`), and any framework-specific attributes.

> **Minimal markup changes:** if a selector needs an anchor point, prefer adding
> an ARIA `role` or `aria-label` to an existing element over introducing new DOM
> nodes or `data-testid` attributes. This improves accessibility and keeps the
> diff small.

---

### Suite F1 — Homepage

- Page loads; a list of plugins is visible (each item shows a name and download count)
- Navigating to the next page shows a different set of plugins
- The page title / heading identifies the site

### Suite F2 — Plugin Detail Page

- Navigate to `/plugin/fields`
- Plugin name, description, at least one version, and author name are present in the page
- Star rating control is present and interactive when logged in
- Watch control toggles its label/state when clicked (logged-in user)

### Suite F3 — Search

- Typing a query and submitting shows a list of results containing the query term
- Each result shows a plugin name and a link to the detail page
- Submitting a query shorter than the minimum length shows an error message

### Suite F4 — Authentication

- **Sign up:** fill the registration form, submit, a confirmation message appears
- **Sign in:** valid credentials → user name appears in the page, panel is accessible
- **Sign in:** wrong password → an error message appears, user is not redirected
- **Sign out:** user name disappears, protected pages redirect to login
- **GitHub OAuth:** the "Login with GitHub" control is present and starts the OAuth flow

### Suite F5 — User Panel

- Authenticated user can reach `/panel` and sees their plugin list
- API keys section lists existing keys and allows creating and deleting a key

### Suite F6 — Plugin Author Panel

- Author reaches `/panel/plugin/:key` and sees the plugin name
- Triggering an XML refresh shows a success or error message
- Updating the XML URL persists the new value (visible after reload)
- Permission list shows current members; adding and removing a user updates the list

---

## Running the Tests

```bash
docker compose -f docker-compose.e2e.yml up -d
cd frontend
npx playwright test
```

---

## CI Integration

```yaml
e2e:
  runs-on: ubuntu-latest
  steps:
    - uses: actions/checkout@v4
    - run: docker compose -f docker-compose.e2e.yml up -d
    - run: cd frontend && npx playwright test
```
