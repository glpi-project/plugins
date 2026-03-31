<?php

declare(strict_types=1);

namespace Tests\Functional;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * Base class for functional tests.
 *
 * Starts a PHP built-in server once per PHP process (shared across all
 * subclasses via static properties on this parent class).  Each test class
 * gets a clean database: tables are truncated and re-seeded in
 * setUpBeforeClass() so test classes are isolated from each other.
 *
 * Prerequisites:
 *  - Configure tests/Functional/config.functional.php with test DB credentials.
 *  - The test DB must be reachable from the CLI (the DB is created
 *    automatically if it does not yet exist).
 *  - pdo_mysql extension must be loaded.
 */
abstract class FunctionalTestCase extends TestCase
{
    private const PORT = 8099;

    // Statics on this class are effectively shared across all subclasses
    // because this class's methods always access them via self::.
    private static ?Process $server  = null;
    private static ?\PDO    $pdo     = null;
    private static string   $apiUrl  = '';

    protected static ?Client $http = null;

    // -------------------------------------------------------------------------
    // PHPUnit lifecycle
    // -------------------------------------------------------------------------

    public static function setUpBeforeClass(): void
    {
        // One-time bootstrap for the whole PHP process
        if (self::$pdo === null) {
            static::connectDatabase();
            static::createSchema();

            $envUrl = getenv('TEST_API_URL');
            if ($envUrl !== false && $envUrl !== '') {
                self::$apiUrl = rtrim($envUrl, '/');
            } else {
                static::startServer();
                self::$apiUrl = 'http://localhost:' . self::PORT;
            }

            register_shutdown_function(static function () {
                if (self::$server !== null) {
                    self::$server->stop();
                }
            });
        }

        // Per-test-class isolation: wipe + re-seed
        static::cleanDatabase();
        static::seedDatabase();

        self::$http = new Client([
            'base_uri'        => self::$apiUrl,
            'http_errors'     => false,   // let tests assert on 4xx/5xx themselves
            'allow_redirects' => false,   // let tests assert on 3xx redirects
        ]);
    }

    // -------------------------------------------------------------------------
    // Infrastructure
    // -------------------------------------------------------------------------

    private static function configFile(): string
    {
        return __DIR__ . '/config.functional.php';
    }

    private static function connectDatabase(): void
    {
        require static::configFile();
        /** @var array $config */
        $db = $config['db_settings'];

        self::$pdo = new \PDO(
            "mysql:host={$db['host']};charset=utf8",
            $db['username'],
            $db['password'],
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );

        $name = $db['database'];
        self::$pdo->exec(
            "CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8 COLLATE utf8_general_ci"
        );
        self::$pdo->exec("USE `{$name}`");
    }

    private static function createSchema(): void
    {
        static::execSqlFile(__DIR__ . '/schema.sql');
    }

    private static function startServer(): void
    {
        $apiRoot = dirname(__DIR__, 2);

        self::$server = new Process(
            ['php', '-S', 'localhost:' . self::PORT, 'index.php'],
            $apiRoot,
            ['APP_CONFIG_FILE' => static::configFile()]
        );
        self::$server->start();

        // Poll until the port accepts connections (max 3 s)
        $deadline = microtime(true) + 3.0;
        while (microtime(true) < $deadline) {
            $sock = @fsockopen('localhost', self::PORT, $errno, $errstr, 0.1);
            if ($sock !== false) {
                fclose($sock);
                return;
            }
            usleep(50_000);
        }

        throw new \RuntimeException(
            "PHP built-in server did not start in time.\nServer output:\n" .
            self::$server->getOutput() . self::$server->getErrorOutput()
        );
    }

    protected static function seedDatabase(): void
    {
        static::execSqlFile(__DIR__ . '/seeds.sql');
    }

    protected static function cleanDatabase(): void
    {
        self::$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach (static::truncatableTables() as $table) {
            self::$pdo->exec("TRUNCATE TABLE `{$table}`");
        }
        self::$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Tables wiped before every test class.  Static reference data (scopes,
     * apps) is re-inserted by seedDatabase() right after.
     */
    protected static function truncatableTables(): array
    {
        return [
            'apps', 'scopes',
            'user', 'sessions', 'access_tokens', 'access_tokens_scopes',
            'refresh_tokens', 'sessions_scopes',
            'user_validation_token', 'user_resetpassword_token',
            'user_external_account',
            'author', 'plugin', 'plugin_author', 'plugin_description',
            'plugin_download', 'plugin_permission', 'plugin_screenshot',
            'plugin_stars', 'plugin_version', 'plugin_xml_fetch_fails',
            'plugin_plugin_lang', 'plugin_tags', 'user_plugin_watch',
            'tag', 'auth_codes', 'message',
        ];
    }

    private static function execSqlFile(string $path): void
    {
        $sql = file_get_contents($path);
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
            self::$pdo->exec($stmt);
        }
    }

    // -------------------------------------------------------------------------
    // Test helpers
    // -------------------------------------------------------------------------

    protected function db(): \PDO
    {
        return self::$pdo;
    }

    /**
     * Insert a plugin row directly (bypasses XML fetch/validation).
     * Returns the inserted data plus `id`.
     */
    protected function createPlugin(array $overrides = []): array
    {
        $defaults = [
            'name'           => 'Test Plugin',
            'key'            => 'testplugin',
            'xml_url'        => 'http://example.com/plugin.xml',
            'download_url'   => 'http://example.com/plugin.zip',
            'active'         => 1,
            'download_count' => 0,
            'date_added'     => date('Y-m-d H:i:s'),
        ];
        $data = array_merge($defaults, $overrides);

        $cols = implode(', ', array_map(static fn ($k) => "`{$k}`", array_keys($data)));
        $vals = implode(', ', array_fill(0, count($data), '?'));
        self::$pdo->prepare("INSERT INTO `plugin` ({$cols}) VALUES ({$vals})")
                  ->execute(array_values($data));

        return array_merge($data, ['id' => (int) self::$pdo->lastInsertId()]);
    }

    /** Insert an author row directly. Returns `id` and `name`. */
    protected function createAuthor(string $name): array
    {
        self::$pdo->prepare("INSERT INTO `author` (`name`) VALUES (?)")->execute([$name]);
        return ['id' => (int) self::$pdo->lastInsertId(), 'name' => $name];
    }

    /** Insert a tag row directly. Returns the inserted data plus `id`. */
    protected function createTag(string $key, string $tag, string $lang = 'en'): array
    {
        self::$pdo->prepare("INSERT INTO `tag` (`key`, `tag`, `lang`) VALUES (?, ?, ?)")
                  ->execute([$key, $tag, $lang]);
        return ['id' => (int) self::$pdo->lastInsertId(), 'key' => $key, 'tag' => $tag, 'lang' => $lang];
    }

    /** Grant a plugin permission to a user directly. */
    protected function grantPermission(int $pluginId, int $userId, bool $admin = false): void
    {
        self::$pdo->prepare(
            "INSERT INTO `plugin_permission` (`plugin_id`, `user_id`, `admin`) VALUES (?, ?, ?)"
        )->execute([$pluginId, $userId, $admin ? 1 : 0]);
    }

    /**
     * Insert a user row directly (bypasses validation and email sending).
     * Returns the inserted data plus `id` and `plain_password`.
     */
    protected function createUser(array $overrides = []): array
    {
        $plainPassword = $overrides['plain_password'] ?? 'Password1';
        unset($overrides['plain_password']);

        $defaults = [
            'username' => 'testuser',
            'email'    => 'testuser@example.com',
            'password' => password_hash($plainPassword, PASSWORD_BCRYPT),
            'realname' => 'Test User',
            'active'   => 1,
        ];
        $data = array_merge($defaults, $overrides);

        $cols = implode(', ', array_map(static fn ($k) => "`{$k}`", array_keys($data)));
        $vals = implode(', ', array_fill(0, count($data), '?'));
        self::$pdo->prepare("INSERT INTO `user` ({$cols}) VALUES ({$vals})")
            ->execute(array_values($data));

        return array_merge($data, [
            'id'             => (int) self::$pdo->lastInsertId(),
            'plain_password' => $plainPassword,
        ]);
    }

    /**
     * Obtain a Bearer token via the password grant.
     * Returns the access_token string, or null if authentication failed.
     */
    protected function getAccessToken(
        string $username,
        string $password,
        array  $scopes = ['plugins', 'user']
    ): ?string {
        $response = self::$http->post('/oauth/authorize', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id'  => 'webapp',
                'username'   => $username,
                'password'   => $password,
                'scope'      => implode(' ', $scopes),
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $body = json_decode((string) $response->getBody(), true);
        return $body['access_token'] ?? null;
    }

    /** Return an Authorization header array for the given Bearer token. */
    protected function bearer(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }
}
