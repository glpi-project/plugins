# Functional Test Specifications

Functional tests exercise the full HTTP stack against a **real test database**.
Requests are sent over HTTP to a real server process — no framework-specific test
client is used — so the suite is portable across Slim, Symfony, or any other PHP
framework.

## Stack

| Tool | Purpose |
|------|---------|
| [PHPUnit](https://phpunit.de/) ≥ 10 | Test runner |
| [Guzzle](https://docs.guzzlephp.org/) (`guzzlehttp/guzzle`) | HTTP client — framework-agnostic |
| PHP built-in server (`php -S`) | Real HTTP server, started as a subprocess |
| MySQL (dedicated test DB) | Test database; SQLite where feasible |
| Database migrations/seeds | Repeatable test fixtures |

**Location:** `api/tests/Functional/`

### Why PHP built-in server + Guzzle

Using a real HTTP server means tests are completely decoupled from the framework
layer. The test code only knows about HTTP — methods, headers, status codes, and
JSON bodies. This makes the suite reusable if the backend framework changes.

A `symfony/process`-managed `php -S` subprocess is started once per suite in
`setUpBeforeClass` and stopped in `tearDownAfterClass`. Each test creates a
`GuzzleHttp\Client` pointed at that server.

```
phpunit
  └── FunctionalTestCase (setUpBeforeClass)
        ├── start: php -S localhost:PORT -t api/public api/public/index.php
        ├── wait for port to be ready
        └── tearDownAfterClass: terminate process
```

### Test database strategy

- Use a dedicated MySQL test database (separate from dev/prod).
- Run migrations once before the suite (`setUpBeforeClass`).
- Seed fixtures before each test class; wrap each test in a transaction and roll
  back in `tearDown` to keep tests isolated.

---

## 2. Authentication (`POST /oauth/authorize`)

### 2.1 Password grant — success
- Authenticating with valid `username` + `password` returns `200` with `access_token`, `refresh_token`, `expires_in`
- Token is stored in the `access_tokens` table

### 2.2 Password grant — failures
- Wrong password returns `401`
- Unknown username returns `401`
- Inactive account returns `401`

### 2.3 Refresh token grant
- A valid `refresh_token` issues a new `access_token` and new `refresh_token`
- An expired or unknown `refresh_token` returns `401`

### 2.4 Client credentials grant
- Valid `client_id` + `client_secret` returns an access token
- Unknown `client_id` returns `401`

---

## 3. User Registration & Validation (`POST /user`, `GET /user/validatemail/:token`)

### 3.1 Registration success
- Creates user with `active = false`
- Creates a `ValidationToken` record
- Returns `200`

### 3.2 Registration validation
- Missing `username` → `400 InvalidField`
- `username` shorter than 4 chars → `400 InvalidField`
- `username` with special characters → `400 InvalidField`
- Duplicate `username` → `400 UnavailableName`
- Invalid `email` format → `400 InvalidField`
- Duplicate `email` → `400 UnavailableName`
- Weak `password` → `400 InvalidField`

### 3.3 Email validation
- Valid token activates user (`active = true`), deletes token, returns access token
- Invalid/unknown token → `400 InvalidValidationToken`

---

## 4. User Profile (`GET /user`, `PUT /user`, `POST /user/delete`)

### 4.1 View profile
- Returns current user's fields including computed `gravatar`
- Missing token → `401`

### 4.2 Edit profile
- `realname`, `website` are updated and persisted
- `password` is hashed before saving
- Changing `email` with an address not in external accounts is silently ignored
- Changing `email` to one verified via GitHub updates it and activates the account

### 4.3 Delete account
- Correct password deletes user, sessions, and access tokens
- Wrong password → `401 InvalidCredentials`
- Short/invalid password format → `400 InvalidField`

---

## 5. Password Reset (`POST /user/sendpasswordresetlink`, `PUT /user/password`)

### 5.1 Send reset link
- Known email creates a `ResetPasswordToken` and returns `200`
- Unknown email → `404 AccountNotFound`
- Missing email field → `400 InvalidField`

### 5.2 Reset password
- Valid token + new password updates password hash, deletes all reset tokens
- Invalid token → `400 WrongPasswordResetToken`
- Missing password → `400 InvalidField`

---

## 6. Plugin Listing

### 6.1 `GET /plugin`
- Returns only `active = 1` plugins
- Response is paginated according to `x-range` header
- `accept-range` and `content-range` headers are set correctly
- Without auth token → `401`

### 6.2 `GET /plugin/popular`, `/plugin/new`, `/plugin/trending`, `/plugin/updated`
- Return only active plugins
- Results are ordered correctly (popular: `download_count DESC`, new: `date_added DESC`)

### 6.3 `GET /plugin/:key`
- Returns all relations (descriptions, authors, versions, screenshots, tags)
- Includes `watched: true` for a user who is watching the plugin
- Inactive plugin → `404 ResourceNotFound`
- Unknown key → `404 ResourceNotFound`

### 6.4 `GET /plugin/rss_new`, `/plugin/rss_updated`
- Returns valid RSS XML without authentication
- Contains at most 30 entries

---

## 7. Plugin Submission (`POST /plugin`)

### 7.1 Validation
- Missing `plugin_url` → `400 InvalidField`
- Duplicate `xml_url` → `400 UnavailableName`
- XML not fetchable → `400 InvalidXML`
- Duplicate plugin `key` in XML → `400 UnavailableName`

### 7.2 Success
- Creates plugin with `active = false`
- Creates a permission entry with `admin = true` for the submitting user
- Returns `{ "success": true }`

---

## 8. Plugin Rating (`POST /plugin/star`)

- Creates a `PluginStar` record
- Returns updated `new_average`
- Non-numeric `plugin_id` or `note` → `400`
- Non-existent plugin → `400`
- Missing required scope → `401`

---

## 9. Plugin Download (`GET /plugin/:key/download`)

- Increments `download_count` by 1
- Creates a `PluginDownload` record
- Redirects to `download_url` with HTTP `301` (when `Accept` is not `application/json`)
- Returns `200` without redirect when `Accept: application/json`

---

## 10. Plugin Permissions

### 10.1 View permissions (`GET /plugin/:key/permissions`)
- Non-admin user → `401 LackPermission`
- Admin user → `200` with array of permission objects

### 10.2 Add permission (`POST /plugin/:key/permissions`)
- Adds permission for a valid username
- Non-existent username → `404 ResourceNotFound`
- Already has permission → `400 RightAlreadyExist`
- Non-admin caller → `401 LackPermission`

### 10.3 Delete permission (`DELETE /plugin/:key/permissions/:username`)
- Non-admin user can remove their own non-admin permission
- Admin cannot delete another admin's permission → `401 CannotDeleteAdmin`
- Non-admin cannot delete another user's permission → `401 LackPermission`
- Non-existent permission → `400 RightDoesntExist`

### 10.4 Modify permission (`PATCH /plugin/:key/permissions/:username`)
- Sets `allowed_refresh_xml`, `allowed_change_xml_url`, `allowed_notifications`
- Invalid `right` value → `400 InvalidField`
- Missing `set` field → `400 InvalidField`
- Non-admin caller → `401 LackPermission`

---

## 11. Author Panel (`GET /panel/plugin/:key`, `POST /panel/plugin/:key`)

### 11.1 View
- User with `admin` flag → `200` with card, tags, statistics
- User with no permission → `401 LackPermission`

### 11.2 Update XML URL
- Valid URL with matching key and authors updates `xml_url`
- URL not fetchable → `400 InvalidXML`
- Key mismatch in new XML → `400 DifferentPluginSignature`
- Author removed in new XML → `400 DifferentPluginSignature`
- Non-URL string → `400 InvalidField`

---

## 12. Authors

### 12.1 `GET /author` / `GET /author/top`
- Returns paginated list
- `GET /author` returns only authors with `plugin_count > 0`

### 12.2 `GET /author/:id`
- Returns `username` and `gravatar` when author is linked to a user account
- Unknown ID → `404 ResourceNotFound`

### 12.3 `GET /author/:id/plugin`
- Returns only the author's active plugins

### 12.4 `POST /claimauthorship`
- Known author name → `200` (email sent to admins)
- Unknown author name → `404 ResourceNotFound`
- Invalid reCAPTCHA → `400 InvalidRecaptcha`

---

## 13. Tags

### 13.1 `GET /tags` / `GET /tags/top`
- Returns tags ordered by `plugin_count DESC`
- Falls back to `en` when no tags exist for requested language

### 13.2 `GET /tags/:id`
- Known tag key → `200` with tag data
- Unknown key → `404 ResourceNotFound`

### 13.3 `GET /tags/:id/plugin`
- Returns plugins that have the specified tag

---

## 14. Search (`POST /search`)

- Returns plugins matching `name`, `key`, `short_description`, or `long_description`
- Query shorter than 2 chars → `400`
- Missing `query_string` → `400`
- Results ordered by `download_count DESC`, `note DESC`, `name ASC`

---

## 15. Version Filter (`GET /version/:version/plugin`)

- Returns plugins compatible with the given GLPI version
- Returns empty list (not error) when no plugins match

---

## 16. User Watches

### 16.1 `POST /user/watchs`
- Creates a `PluginWatch` record
- Unknown `plugin_key` → `404 ResourceNotFound`
- Already watched → `400 AlreadyWatched`

### 16.2 `DELETE /user/watchs/:key`
- Removes the watch record
- Not watching → `404`

### 16.3 `GET /user/watchs`
- Returns an array of plugin keys

---

## 17. User Search (`POST /user/search`)

- Matches on `username`, `realname` (partial), or `email` (exact)
- Missing `search` field → `400 InvalidField`
- Results include only `username` and `realname`

---

## 18. External Accounts

### 18.1 `GET /user/external_accounts`
- Returns list of linked external accounts for current user

### 18.2 `DELETE /user/external_accounts/:id`
- Removes the link
- Last external account with no password set → `401 NoCredentialsLeft`

---

## 19. User Apps

### 19.1 `POST /user/apps`
- Creates app with random `client_id` and `secret`
- Duplicate name (same user) → `400 UnavailableName`
- Invalid name → `400 InvalidField`

### 19.2 `GET /user/apps`, `GET /user/apps/:id`
- Returns only apps belonging to the current user
- Unknown app ID → `404 ResourceNotFound`

### 19.3 `PUT /user/apps/:id`
- Updates modifiable fields; auto-generated fields unchanged

### 19.4 `DELETE /user/apps/:id`
- Removes the app record

---

## 20. Contact Messages (`POST /message`)

- Creates a `Message` record in the database
- Missing required contact field → `400 MissingField`
- Invalid email format → `400 InvalidField`
- Failed reCAPTCHA → `400 InvalidRecaptcha`

---

## Running the Tests

```bash
cd api
cp config.example.php config.test.php  # set test DB credentials
APP_ENV=test vendor/bin/phpunit --configuration phpunit.xml --testsuite Functional
```

### Base test case skeleton

```php
abstract class FunctionalTestCase extends \PHPUnit\Framework\TestCase
{
    private static \Symfony\Component\Process\Process $server;
    protected static GuzzleHttp\Client $http;

    public static function setUpBeforeClass(): void
    {
        // Start PHP built-in server
        self::$server = new \Symfony\Component\Process\Process(
            ['php', '-S', 'localhost:8099', 'public/index.php'],
            __DIR__ . '/../../'
        );
        self::$server->start();
        // Wait until the port accepts connections
        usleep(200_000);

        self::$http = new \GuzzleHttp\Client([
            'base_uri'        => 'http://localhost:8099',
            'http_errors'     => false,   // don't throw on 4xx/5xx
            'allow_redirects' => false,
        ]);
    }

    public static function tearDownAfterClass(): void
    {
        self::$server->stop();
    }
}
```

> `http_errors: false` is important — it lets tests assert on 4xx/5xx responses
> instead of catching Guzzle exceptions.

> `allow_redirects: false` is important for endpoints that return HTTP `301`
> (e.g. plugin download) so the redirect itself can be asserted.
