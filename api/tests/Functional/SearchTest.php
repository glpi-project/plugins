<?php

declare(strict_types=1);

namespace Tests\Functional;

/**
 * Functional tests for POST /search (§14).
 */
class SearchTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'plugin', 'plugin_description', 'plugin_stars',
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
        return $this->getAccessToken($user['username'], $user['plain_password'], ['plugins:search']);
    }

    public function testSearchMatchesOnPluginName(): void
    {
        $this->createPlugin(['key' => 'uniquepluginname', 'name' => 'UniquePluginName', 'active' => 1]);
        $token = $this->authedToken();

        $response = self::$http->post('/search', [
            'headers' => $this->bearer($token),
            'json'    => ['query_string' => 'UniquePlugin'],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $keys = array_column($body, 'key');
        $this->assertContains('uniquepluginname', $keys);
    }

    public function testSearchMatchesOnPluginKey(): void
    {
        $this->createPlugin(['key' => 'searchbykey', 'name' => 'Search By Key Plugin', 'active' => 1]);
        $token = $this->authedToken();

        $response = self::$http->post('/search', [
            'headers' => $this->bearer($token),
            'json'    => ['query_string' => 'searchbykey'],
        ]);

        $body = json_decode((string) $response->getBody(), true);
        $keys = array_column($body, 'key');
        $this->assertContains('searchbykey', $keys);
    }

    public function testSearchExcludesInactivePlugins(): void
    {
        $this->createPlugin(['key' => 'inactivesearch', 'name' => 'InactiveSearchPlugin', 'active' => 0]);
        $token = $this->authedToken();

        $response = self::$http->post('/search', [
            'headers' => $this->bearer($token),
            'json'    => ['query_string' => 'InactiveSearch'],
        ]);

        $body = json_decode((string) $response->getBody(), true);
        $keys = array_column($body, 'key');
        $this->assertNotContains('inactivesearch', $keys);
    }

    public function testShortQueryReturns400(): void
    {
        $token = $this->authedToken();

        $response = self::$http->post('/search', [
            'headers' => $this->bearer($token),
            'json'    => ['query_string' => 'a'],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testMissingQueryStringReturns400(): void
    {
        $token = $this->authedToken();

        $response = self::$http->post('/search', [
            'headers' => $this->bearer($token),
            'json'    => [],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }
}
