# Review Response — PR #88 (E2E Test Suite)

This document records the decisions taken in response to the review of PR #88.
Each numbered point matches the reviewer's numbering.

---

## Critical Issues

### 1. Raw CSS selectors in spec files — **FIXED**

All raw CSS selectors have been replaced.

**`auth.spec.js` — submit button (lines 33, 45, 61)**

The reviewer suggested `getByRole('button', { name: /sign.?in/i })`, but the header
user-menu also renders a "Sign in" button, causing a selector conflict.
Fix: add `data-testid="signin-submit-button"` to the `<md-button type="submit">` in
`signin.html` and target it via `[data-testid="signin-submit-button"]`. This is
consistent with how the other inputs in the signin form already use `data-testid`.

**`auth.spec.js` — toast selector (lines 49–50)**

`md-toast, .md-toast-content` is AngularJS Material-specific.
Fix: `page.getByText(/wrong credentials/i)` — checks the actual translated message
text, consistent with how the other toast assertions in the file work
(`'You are now successfully logged in'`, `'You are now disconnected'`).
Angular Material 1's `$mdToast` does not render with `role="alert"`, so
`getByRole('alert')` does not match.

**`plugin.spec.js` — plugin detail selectors (lines 12, 16, 20, 24)**

Added `data-testid` attributes to `plugin.html`:
- `data-testid="plugin-name"` on `<h2>{{plugin.name}}</h2>`
- `data-testid="plugin-authors"` on `<h4 class="inline-authors">`
- `data-testid="version-badge"` on the `<span class="pill bg_lightblue">` items
- `data-testid="plugin-description"` on the `<div class="markdown">` container

**`panel.spec.js` — panel selectors (lines 19, 40, 64)**

Added `data-testid` attributes to `panel.html` and `pluginpanel.html`:
- `data-testid="panel-plugin-name"` on `<h4 class="plugin-name">` in `panel.html`
- `data-testid="plugin-panel-header-name"` on `<h2 class="plugin-name">` in `pluginpanel.html`
- `data-testid="xml-errors"` on `<md-content class="xml-errors">` in `pluginpanel.html`

---

### 2. Root DB credentials in `docker-compose.e2e.yml` — **FIXED**

Added a dedicated MySQL user `e2euser`/`e2epass` with `GRANT ALL ON glpi_plugins_e2e.*`.
The `api` service now uses `TEST_DB_USER: e2euser` / `TEST_DB_PASS: e2epass`.

---

### 3. CI wait loop exits 0 on frontend failure — **FIXED**

Added `|| exit 1` after the loop in `.github/workflows/tests_e2e.yml` so the job
fails immediately if the frontend never becomes reachable.

---

## Moderate Issues

### 4. `utf8` / `utf8_general_ci` in `api/config.e2e.php` — **FIXED**

Changed to `utf8mb4` / `utf8mb4_unicode_ci` to match production and avoid data
truncation on 4-byte Unicode characters.

---

### 5. `frontend` has no health-check on `api` — **FIXED**

Added a healthcheck to the `api` service in `docker-compose.e2e.yml` and updated
`frontend`'s `depends_on` to `condition: service_healthy`.

---

### 6. XML URL test mutates shared DB state — **FIXED**

The `'XML URL field is editable and persists on save'` test in `panel.spec.js` now
stores the original XML URL before the test and restores it in an `afterAll` hook,
preventing state leakage to subsequent tests.

---

### 7. `api` Docker hostname assumed reachable — **DOCUMENTED**

The `updatedXmlUrl` pointing to `http://api/...` relies on the PHP container being
able to reach itself via the `api` Docker service hostname. This is guaranteed by
Docker Compose's internal DNS when all services are on the same network. A comment
has been added to `panel.spec.js` to make this assumption explicit.

---

### 8. Sign-up toast may be dismissed before assertion — **FIXED**

The sign-up test in `auth.spec.js` previously called `waitForURL('/#/')` before
checking for the toast. The assertion order has been swapped: the toast is checked
first (immediately after clicking submit), then the URL redirect is verified.

---

## Minor Issues

### 9. Duplicate `data-testid="featured-plugin-item"` — **FIXED**

Each featured section already uses a distinct `ng-repeat` variable, so the fix is
straightforward. Replaced the shared testid with section-specific values in
`featured.html`:

- Trending → `data-testid="trending-plugin-item"`
- New      → `data-testid="new-plugin-item"`
- Popular  → `data-testid="popular-plugin-item"`
- Updated  → `data-testid="updated-plugin-item"`

`homepage.spec.js` updated accordingly: the "each section has at least one item"
test now verifies each list individually, and the "click navigates" test anchors
on `[data-testid="trending-plugin-item"]`.

---

### 10. `--ignore-platform-reqs` in CI workflow — **FIXED**

Removed from the `composer install` step. The CI environment uses PHP 7.4, which
matches the project's requirements; the flag was suppressing useful validation.

---

### 11. `networkidle` may be unreliable for polling apps — **ARGUED / KEPT**

The AngularJS app uses `$http` (request/response), not persistent polling,
WebSockets, or Server-Sent Events. All in-flight requests complete in finite time,
so `networkidle` (500 ms of quiet) is reliable in practice. Switching to
`waitForResponse` / `waitForFunction` on a specific API call would add coupling
between the helper and individual page logic. `networkidle` is kept.

---

### 12. Node.js 10 EOL and missing lockfile in Dockerfile — **ACKNOWLEDGED / OUT OF SCOPE**

Node 10 is intentional: the legacy frontend build chain (Grunt + Bower + Ruby
Compass) requires it. Upgrading Node is a separate modernisation step. The lockfile
concern (non-deterministic `npm install`) is valid and will be addressed when the
Node version is bumped, as part of the same effort.
