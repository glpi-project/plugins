<?php

declare(strict_types=1);

namespace Tests\Functional;

/**
 * Functional tests for GET /version/:version/plugin (§15).
 */
class VersionTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'plugin', 'plugin_version', 'plugin_description', 'plugin_stars',
            'user', 'sessions', 'access_tokens', 'access_tokens_scopes',
            'refresh_tokens', 'sessions_scopes',
        ] as $t) {
            $pdo->exec("TRUNCATE TABLE `{$t}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    private function authedToken(): string
    {
        $user = $this->createUser();
        return $this->getAccessToken($user['username'], $user['plain_password'], ['version', 'plugins']);
    }

    public function testReturnsPluginsCompatibleWithVersion(): void
    {
        $plugin = $this->createPlugin(['key' => 'versionplug', 'name' => 'Version Plugin', 'active' => 1]);
        $this->db()->prepare(
            "INSERT INTO `plugin_version` (`plugin_id`, `num`, `compatibility`) VALUES (?, ?, ?)"
        )->execute([$plugin['id'], '1.0.0', '9.5']);
        $token = $this->authedToken();

        $response = self::$http->get('/version/9.5/plugin', ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $keys = array_column($body, 'key');
        $this->assertContains('versionplug', $keys);
    }

    public function testReturnsEmptyListWhenNoPluginsMatchVersion(): void
    {
        $token = $this->authedToken();

        $response = self::$http->get('/version/0.0.1/plugin', ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame([], $body);
    }

    public function testDoesNotReturnPluginsForDifferentVersion(): void
    {
        $plugin = $this->createPlugin(['key' => 'wrongversionplug', 'name' => 'Wrong Version', 'active' => 1]);
        $this->db()->prepare(
            "INSERT INTO `plugin_version` (`plugin_id`, `num`, `compatibility`) VALUES (?, ?, ?)"
        )->execute([$plugin['id'], '2.0.0', '10.0']);
        $token = $this->authedToken();

        $response = self::$http->get('/version/9.5/plugin', ['headers' => $this->bearer($token)]);

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $keys = array_column($body, 'key');
        $this->assertNotContains('wrongversionplug', $keys);
    }
}
