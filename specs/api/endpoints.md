# API Endpoint Reference

Base URL: `https://<host>/api`

## Authentication

All authenticated endpoints require a Bearer token in the `Authorization` header:

```
Authorization: Bearer <access_token>
```

Tokens are issued by `POST /oauth/authorize` using one of three OAuth2 grant types:
- `password` — user login (username/password)
- `client_credentials` — API app key
- `refresh_token` — token renewal

### Scopes

| Scope | Description |
|-------|-------------|
| `plugins` | Browse plugin list |
| `plugins:search` | Search plugins |
| `plugin:card` | Read single plugin details |
| `plugin:star` | Rate a plugin |
| `plugin:submit` | Submit a new plugin |
| `plugin:download` | Download a plugin |
| `tags` | Browse tag list |
| `tag` | Read single tag |
| `authors` | Browse author list |
| `author` | Read single author |
| `version` | Filter by GLPI version |
| `user` | Read/edit own profile |
| `user:apps` | Manage own API apps |
| `user:externalaccounts` | Manage linked OAuth accounts |
| `users:search` | Search users |
| `message` | Send contact message |

### Pagination

Paginated endpoints support range-based pagination via HTTP headers:

**Request header:**
```
x-range: 0-14
```

**Response headers:**
```
accept-range: model 100
content-range: 0-14/100
```

HTTP status `206 Partial Content` is returned for partial results, `200 OK` for the complete set.

Default page size: **15 items**.

### Language

Include the `x-lang` header to get localized plugin descriptions:

```
x-lang: fr
```

Supported values: `en`, `fr`, `es`. Any other value falls back to `en`.

---

## OAuth & Authorization

### `POST /oauth/authorize`

Issue an access token.

**No auth required.**

**Request body (password grant):**
```json
{
  "grant_type": "password",
  "username": "john",
  "password": "secret",
  "client_id": "<app_client_id>",
  "client_secret": "<app_secret>"
}
```

**Request body (refresh_token grant):**
```json
{
  "grant_type": "refresh_token",
  "refresh_token": "<token>",
  "client_id": "<app_client_id>",
  "client_secret": "<app_secret>"
}
```

**Request body (client_credentials grant):**
```json
{
  "grant_type": "client_credentials",
  "client_id": "<app_client_id>",
  "client_secret": "<app_secret>"
}
```

**Response `200`:**
```json
{
  "access_token": "...",
  "refresh_token": "...",
  "expires_in": 3600,
  "token_type": "Bearer"
}
```

---

### `GET /oauth/associate/:service`

OAuth2 callback from an external provider (currently: `github`). Handles three flows:

1. **New user** — creates a GLPI account from external account info, returns tokens
2. **Link account** — links external account to the currently authenticated user (pass `access_token` via cookie)
3. **Returning user** — logs in and returns tokens if external account is already linked

**No auth required.** Returns an HTML page that posts a `window.postMessage` with the result.

**Path parameter:** `service` = `github`

**Response data (in postMessage payload):**
```json
{
  "access_token": "...",
  "refresh_token": "...",
  "access_token_expires_in": 3600,
  "account_created": true,
  "external_account_linked": true
}
```

---

### `GET /oauth/available_emails`

Returns all email addresses available through the user's linked external accounts.

**Scopes required:** `user`

**Response `200`:**
```json
[
  { "email": "user@example.com", "service": "github" }
]
```

---

## Users

### `POST /user`

Register a new account. Sends a confirmation email to the provided address.

**No auth required.**

**Request body:**
```json
{
  "username": "john",
  "email": "john@example.com",
  "password": "secret123",
  "realname": "John Doe",
  "location": "Paris",
  "website": "https://example.com"
}
```

| Field | Required | Validation |
|-------|----------|------------|
| `username` | Yes | 4–28 chars, alphanumeric only |
| `email` | Yes | Valid email format, unique |
| `password` | Yes | Must pass `User::isValidPassword()` |
| `realname` | No | 4+ chars, alphanumeric + spaces |
| `location` | No | Non-empty string |
| `website` | No | Valid URL |

**Response `200`:** empty body (email sent)

**Errors:**
- `400 InvalidField` — invalid username/email/password format
- `400 UnavailableName` — username or email already taken

---

### `GET /user`

Get the current authenticated user's profile.

**Scopes required:** `user`

**Response `200`:**
```json
{
  "id": 1,
  "username": "john",
  "email": "john@example.com",
  "realname": "John Doe",
  "location": "Paris",
  "website": "https://example.com",
  "gravatar": "<md5_of_email>",
  "active": true
}
```

---

### `PUT /user`

Edit the current user's profile. All fields are optional.

**Scopes required:** `user`

**Request body:**
```json
{
  "email": "newemail@example.com",
  "password": "newpassword",
  "realname": "New Name",
  "website": "https://new-site.com"
}
```

Note: changing `email` is only allowed if that address is verified through a linked external account.

**Response `200`:** updated user object

---

### `POST /user/delete`

Delete the current user's account. Requires password confirmation.

**Scopes required:** `user`

**Request body:**
```json
{ "password": "current_password" }
```

**Response `200`:** empty body

**Errors:**
- `401 InvalidCredentials` — wrong password

---

### `GET /user/validatemail/:token`

Validates the user's email address using the token from the confirmation email. Activates the account and returns an access token.

**No auth required.**

**Path parameter:** `token` — validation token from email

**Response `200`:**
```json
{
  "access_token": "...",
  "refresh_token": "...",
  "expires_in": 3600
}
```

**Errors:**
- `400 InvalidValidationToken` — token not found

---

### `POST /user/sendpasswordresetlink`

Sends a password reset link to the provided email address.

**No auth required.**

**Request body:**
```json
{ "email": "john@example.com" }
```

**Response `200`:** empty body

**Errors:**
- `400 InvalidField` — missing/invalid email
- `404 AccountNotFound` — no account with that email

---

### `PUT /user/password`

Reset password using a token received by email.

**No auth required.**

**Request body:**
```json
{
  "token": "<reset_token>",
  "password": "new_password"
}
```

**Response `200`:** empty body

**Errors:**
- `400 WrongPasswordResetToken` — token missing or not found
- `400 InvalidField` — invalid password

---

### `GET /user/plugins`

List plugins the current user has permissions on (active plugins only).

**Scopes required:** `user`, `plugins`

**Response `200`:** array of plugin objects

---

### `GET /user/watchs`

List plugin keys that the current user is watching.

**Scopes required:** `user`, `plugins`

**Response `200`:**
```json
["myplugin", "anotherplugin"]
```

---

### `POST /user/watchs`

Start watching a plugin.

**Scopes required:** `user`, `plugins`

**Request body:**
```json
{ "plugin_key": "myplugin" }
```

**Response `200`:** empty body

**Errors:**
- `400 InvalidField` — missing plugin_key
- `404 ResourceNotFound` — plugin not found
- `400 AlreadyWatched` — already watching this plugin

---

### `DELETE /user/watchs/:key`

Stop watching a plugin.

**Scopes required:** `user`, `plugins`

**Path parameter:** `key` — plugin key

**Response `200`:** empty body
**Response `404`:** plugin not found or not watching

---

### `POST /user/search`

Search users by username, realname, or exact email.

**Scopes required:** `users:search`

**Request body:**
```json
{ "search": "john" }
```

**Response `200`:**
```json
[
  { "username": "john", "realname": "John Doe" }
]
```

---

### `GET /user/external_accounts`

List all external OAuth accounts linked to the current user.

**Scopes required:** `user:externalaccounts`

**Response `200`:**
```json
[
  { "id": 1, "service": "github", "external_user_id": "12345" }
]
```

---

### `DELETE /user/external_accounts/:id`

Unlink an external OAuth account.

**Scopes required:** `user:externalaccounts`

**Path parameter:** `id` — external account ID

**Response `200`:** empty body

**Errors:**
- `401 NoCredentialsLeft` — cannot remove last external account when no password is set

---

## User Apps (API Keys)

### `GET /user/apps`

List all API applications created by the current user.

**Scopes required:** `user`, `user:apps`

**Response `200`:** array of app objects

---

### `GET /user/apps/:id`

Get details of a specific app.

**Scopes required:** `user`, `user:apps`

**Response `200`:**
```json
{
  "id": 1,
  "name": "My App",
  "homepage_url": "https://myapp.com",
  "description": "A great app",
  "client_id": "...",
  "secret": "..."
}
```

**Errors:**
- `404 ResourceNotFound` — app not found

---

### `POST /user/apps`

Create a new API application. Generates a `client_id` and `secret` automatically.

**Scopes required:** `user`, `user:apps`

**Request body:**
```json
{
  "name": "My App",
  "homepage_url": "https://myapp.com",
  "description": "A great app"
}
```

| Field | Required | Validation |
|-------|----------|------------|
| `name` | Yes | Must pass `App::isValidName()`, unique per user |
| `homepage_url` | No | Valid URL |
| `description` | No | Must pass `App::isValidDescription()` |

**Errors:**
- `400 InvalidField` — invalid name/url/description
- `400 UnavailableName` — app name already taken

---

### `PUT /user/apps/:id`

Update an existing app.

**Scopes required:** `user`, `user:apps`

**Request body:** same optional fields as `POST /user/apps`

**Response `200`:** updated app object

**Errors:**
- `404 ResourceNotFound` — app not found

---

### `DELETE /user/apps/:id`

Delete an API application.

**Scopes required:** `user`, `user:apps`

**Response `200`:** empty body

**Errors:**
- `404 ResourceNotFound` — app not found

---

## Plugins

### `GET /plugin`

List all active plugins, paginated. Sorted by default ordering.

**Scopes required:** `plugins`

**Response `206/200`:** paginated array of plugin objects with authors, versions, descriptions

---

### `POST /plugin`

Submit a new plugin for review.

**Scopes required:** `plugin:submit`

**Request body:**
```json
{
  "plugin_url": "https://example.com/plugin.xml",
  "recaptcha_response": "<recaptcha_token>"
}
```

The XML at `plugin_url` is fetched and validated. The plugin `key` must be unique.

**Response `200`:**
```json
{ "success": true }
```

**Errors:**
- `400 InvalidRecaptcha` — reCAPTCHA failed
- `400 InvalidField` — missing or invalid plugin_url
- `400 UnavailableName` — XML URL or plugin key already exists
- `400 InvalidXML` — XML unreachable or invalid

---

### `GET /plugin/new`

Most recently added plugins, paginated.

**Scopes required:** `plugins`

**Response `206/200`:** paginated array of plugin objects

---

### `GET /plugin/popular`

Most downloaded plugins, paginated.

**Scopes required:** `plugins`

---

### `GET /plugin/trending`

Trending plugins (most downloaded in the last 2 weeks), paginated.

**Scopes required:** `plugins`

---

### `GET /plugin/updated`

Most recently updated plugins, paginated.

**Scopes required:** `plugins`

---

### `GET /plugin/rss_new`

RSS feed of the 30 newest plugins.

**No auth required.** Returns RSS XML.

---

### `GET /plugin/rss_updated`

RSS feed of the 30 most recently updated plugins.

**No auth required.** Returns RSS XML.

---

### `POST /plugin/star`

Rate a plugin.

**Scopes required:** `plugin:star`

**Request body:**
```json
{
  "plugin_id": 42,
  "note": 4
}
```

**Response `200`:**
```json
{ "new_average": 4.2 }
```

**Errors:**
- `400` — missing or non-numeric `plugin_id` or `note`
- `400` — plugin does not exist

---

### `GET /plugin/:key`

Get full details for a single active plugin.

**Scopes required:** `plugin:card`

**Path parameter:** `key` — unique plugin key (e.g. `fields`)

**Response `200`:**
```json
{
  "id": 1,
  "key": "fields",
  "name": "Additional Fields",
  "logo_url": "...",
  "xml_url": "...",
  "download_count": 15000,
  "note": 4.5,
  "nb_votes": 120,
  "watched": false,
  "descriptions": [...],
  "authors": [...],
  "versions": [...],
  "screenshots": [...],
  "tags": [...],
  "langs": [...]
}
```

**Errors:**
- `404 ResourceNotFound` — plugin not found or inactive

---

### `GET /plugin/:key/download`

Track a download and redirect to the plugin's download URL (HTTP 301).

**No auth required.**

When `Accept: application/json` header is set, tracking happens but no redirect is issued.

---

### `GET /plugin/:key/permissions`

List users who have permissions on the plugin.

**Scopes required:** `user`, `plugin:card`

**Auth requirement:** caller must have `admin` flag on the plugin.

**Response `200`:** array of user/permission objects

---

### `POST /plugin/:key/permissions`

Grant a user access to the plugin.

**Scopes required:** `user`, `plugin:card`

**Auth requirement:** caller must have `admin` flag on the plugin.

**Request body:**
```json
{ "username": "jane" }
```

**Errors:**
- `400 InvalidField` — missing username
- `404 ResourceNotFound` — user not found
- `400 RightAlreadyExist` — user already has a permission entry

---

### `DELETE /plugin/:key/permissions/:username`

Remove a user's permission on the plugin.

**Scopes required:** `user`, `plugin:card`

**Auth requirement:** caller must be `admin`, unless removing their own (non-admin) permission.

**Errors:**
- `400 RightDoesntExist` — user has no permission on this plugin
- `401 CannotDeleteAdmin` — cannot remove an admin's permission

---

### `PATCH /plugin/:key/permissions/:username`

Modify a specific permission flag for a user on a plugin.

**Scopes required:** `user`, `plugin:card`

**Auth requirement:** caller must have `admin` flag on the plugin.

**Request body:**
```json
{
  "right": "allowed_refresh_xml",
  "set": true
}
```

| `right` values |
|----------------|
| `allowed_refresh_xml` |
| `allowed_change_xml_url` |
| `allowed_notifications` |

**Errors:**
- `400 InvalidField` — invalid `right` or `set` value

---

### `POST /plugin/:key/refresh_xml`

Re-fetch and update the plugin data from its XML URL.

**Scopes required:** `user`, `plugin:card`

**Auth requirement:** caller must have `admin` or `allowed_refresh_xml` flag.

**Response `200`:**
```json
{
  "errors": [],
  "xml_state": { ... }
}
```

**Errors in response body (not HTTP errors):**
- XML URL unreachable
- XML parse error
- XML field validation errors (collected in array)

---

## Panel (Author Mode)

### `GET /panel/plugin/:key`

Author dashboard view of a plugin. Includes tags and stub statistics.

**Scopes required:** `plugin:card`, `user`

**Auth requirement:** caller must have `admin`, `allowed_refresh_xml`, or `allowed_change_xml_url` flag.

**Response `200`:**
```json
{
  "card": { ...plugin object... },
  "tags": [...],
  "statistics": {
    "current_monthly_downloads": 500,
    "current_weekly_downloads": 250
  }
}
```

---

### `POST /panel/plugin/:key`

Update the plugin's XML URL.

**Scopes required:** `user`, `plugin:card`

**Auth requirement:** caller must have `admin` or `allowed_change_xml_url` flag.

**Request body:**
```json
{ "xml_url": "https://example.com/new-plugin.xml" }
```

The new URL is validated:
1. Must be a valid URL
2. Must be fetchable over HTTP
3. XML must be valid and parseable
4. Plugin `key` in XML must match the existing key
5. All existing authors must still be present in the new XML

**Response `200`:** empty body

---

## Authors

### `GET /author`

List all authors who have contributed to at least one plugin, paginated.

**Scopes required:** `authors`

---

### `GET /author/top`

List top authors (all, including non-contributors), paginated.

**Scopes required:** `authors`

---

### `GET /author/:id`

Get details for a specific author. Includes gravatar hash if linked to a user account.

**Scopes required:** `author`

**Path parameter:** `id` — author ID (integer)

**Response `200`:**
```json
{
  "id": 1,
  "name": "John Doe",
  "plugin_count": 5,
  "username": "john",
  "gravatar": "<md5>"
}
```

---

### `GET /author/:id/plugin`

List plugins by a specific author, paginated.

**Scopes required:** `author`, `plugins`

---

### `POST /claimauthorship`

Send a request to admins to claim authorship of a plugin author record.

**Scopes required:** `user`

**Request body:**
```json
{
  "author": "John Doe",
  "recaptcha_response": "<token>"
}
```

**Response `200`:** empty body (email sent to admins)

**Errors:**
- `400 InvalidRecaptcha` — reCAPTCHA failed
- `400 InvalidField` — missing/invalid author name
- `404 ResourceNotFound` — author name not found

---

## Tags

### `GET /tags`

List all tags ordered by usage count, paginated. Automatically falls back to `en` if no tags in requested language.

**Scopes required:** `tags`

**Response `206/200`:** paginated array of tag objects

---

### `GET /tags/top`

Same as `GET /tags`. Returns top tags by plugin count.

**Scopes required:** `tags`

---

### `GET /tags/:id`

Get a single tag by key.

**Scopes required:** `tag`

**Path parameter:** `id` — tag key (string, e.g. `inventory`)

**Response `200`:**
```json
{ "id": 1, "key": "inventory", "tag": "Inventory", "plugin_count": 12 }
```

**Errors:**
- `404 ResourceNotFound` — tag key not found

---

### `GET /tags/:id/plugin`

List active plugins associated with a tag, paginated.

**Scopes required:** `tag`, `plugins`

---

## Search

### `POST /search`

Full-text search across plugin names, keys, and descriptions.

**Scopes required:** `plugins:search`

**Request body:**
```json
{ "query_string": "inventory" }
```

Minimum length: **2 characters**.

Results are ordered by `download_count DESC`, `note DESC`, `name ASC`.

**Response `206/200`:** paginated array of plugin objects

**Errors:**
- `400` — query_string missing or too short

---

## Versions

### `GET /version/:version/plugin`

List plugins compatible with a given GLPI version, paginated.

**Scopes required:** `version`, `plugins`

**Path parameter:** `version` — GLPI version string (e.g. `9.5`, `10.0`)

**Response `206/200`:** paginated array of plugin objects

---

## Messages

### `POST /message`

Send a contact message to the site administrators. Saved to DB and emailed.

**Scopes required:** `message`

**Request body:**
```json
{
  "recaptcha_response": "<token>",
  "contact": {
    "firstname": "John",
    "lastname": "Doe",
    "email": "john@example.com",
    "subject": "Question about a plugin",
    "message": "Hello, I have a question..."
  }
}
```

| Field | Max length |
|-------|-----------|
| `firstname` | 45 chars |
| `lastname` | 45 chars |
| `subject` | 280 chars |
| `message` | 16000 chars |

**Response `200`:**
```json
{ "success": true }
```

**Errors:**
- `400 InvalidRecaptcha` — reCAPTCHA failed
- `400 MissingField` — required contact field missing
- `400 InvalidField` — field validation failed

---

## Error Response Format

All errors return JSON with an HTTP status ≥ 400:

```json
{
  "error": "RESOURCE_NOT_FOUND(type=Plugin, key=myplugin)"
}
```

All errors use a single `error` field containing the string representation of the exception. Extra context (resource type, field name, etc.) is encoded inline in that string.

Common HTTP status codes used:
- `400 Bad Request` — invalid input
- `401 Unauthorized` — missing or invalid token, or insufficient scopes/permissions
- `404 Not Found` — resource does not exist
- `500 Internal Server Error` — unexpected server error
