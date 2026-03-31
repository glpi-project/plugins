<?php

declare(strict_types=1);

namespace Tests\Functional\Plugin;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for plugin listing endpoints (§6).
 *
 * GET /plugin
 * GET /plugin/popular, /plugin/new, /plugin/trending, /plugin/updated
 * GET /plugin/:key
 * GET /plugin/rss_new, /plugin/rss_updated
 */
class ListingTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'plugin', 'plugin_author', 'plugin_description', 'plugin_version',
            'plugin_download', 'plugin_stars', 'plugin_tags', 'plugin_screenshot',
            'plugin_permission', 'user_plugin_watch',
            'user', 'sessions', 'access_tokens', 'access_tokens_scopes',
            'refresh_tokens', 'sessions_scopes',
        ] as $t) {
            $pdo->exec("TRUNCATE TABLE `{$t}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    private function authedToken(array $scopes = ['plugins']): string
    {
        $user = $this->createUser();
        return $this->getAccessToken($user['username'], $user['plain_password'], $scopes);
    }

    // -------------------------------------------------------------------------
    // GET /plugin (§6.1)
    // -------------------------------------------------------------------------

    public function testListReturnsOnlyActivePlugins(): void
    {
        $this->createPlugin(['key' => 'activeplug',   'name' => 'Active Plugin',   'active' => 1]);
        $this->createPlugin(['key' => 'inactiveplug', 'name' => 'Inactive Plugin', 'active' => 0]);
        $token = $this->authedToken(['plugins']);

        $response = self::$http->get('/plugin', ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $keys = array_column($body, 'key');
        $this->assertContains('activeplug', $keys);
        $this->assertNotContains('inactiveplug', $keys);
    }

    public function testListWithoutTokenReturns401(): void
    {
        $response = self::$http->get('/plugin');
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testListResponseHasPaginationHeaders(): void
    {
        $this->createPlugin(['key' => 'pagplug', 'name' => 'Pag Plugin', 'active' => 1]);
        $token = $this->authedToken(['plugins']);

        $response = self::$http->get('/plugin', ['headers' => $this->bearer($token)]);

        $this->assertTrue($response->hasHeader('accept-range'), 'accept-range header must be present');
        $this->assertTrue($response->hasHeader('content-range'), 'content-range header must be present');
    }

    public function testXRangeHeaderRestrictsResults(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->createPlugin(['key' => "rangeplug{$i}", 'name' => "Range Plugin {$i}", 'active' => 1]);
        }
        $token = $this->authedToken(['plugins']);

        $response = self::$http->get('/plugin', [
            'headers' => array_merge($this->bearer($token), ['x-range' => '0-1']),
        ]);

        $body = json_decode((string) $response->getBody(), true);
        $this->assertCount(2, $body, 'x-range: 0-1 should return 2 items');
        $this->assertSame(206, $response->getStatusCode());
    }

    public function testRangeStartingBeyondTotalReturns400(): void
    {
        $this->createPlugin(['key' => 'onlyone', 'name' => 'Only One', 'active' => 1]);
        $token = $this->authedToken(['plugins']);

        $response = self::$http->get('/plugin', [
            'headers' => array_merge($this->bearer($token), ['x-range' => '999-1000']),
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // GET /plugin/popular, /plugin/new, /plugin/updated (§6.2)
    // -------------------------------------------------------------------------

    public function testPopularReturnsOnlyActivePlugins(): void
    {
        $this->createPlugin(['key' => 'activepop',   'name' => 'Active Popular',   'active' => 1, 'download_count' => 100]);
        $this->createPlugin(['key' => 'inactivepop', 'name' => 'Inactive Popular', 'active' => 0, 'download_count' => 200]);
        $token = $this->authedToken(['plugins']);

        $response = self::$http->get('/plugin/popular', ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $keys = array_column($body, 'key');
        $this->assertNotContains('inactivepop', $keys);
    }

    public function testNewReturnsOnlyActivePlugins(): void
    {
        $this->createPlugin(['key' => 'activenew',   'name' => 'Active New',   'active' => 1]);
        $this->createPlugin(['key' => 'inactivenew', 'name' => 'Inactive New', 'active' => 0]);
        $token = $this->authedToken(['plugins']);

        $response = self::$http->get('/plugin/new', ['headers' => $this->bearer($token)]);

        $body = json_decode((string) $response->getBody(), true);
        $keys = array_column($body, 'key');
        $this->assertNotContains('inactivenew', $keys);
    }

    // -------------------------------------------------------------------------
    // GET /plugin/:key (§6.3)
    // -------------------------------------------------------------------------

    public function testGetSinglePluginReturns200(): void
    {
        $plugin = $this->createPlugin(['key' => 'singleplug', 'name' => 'Single Plugin', 'active' => 1]);
        $token  = $this->authedToken(['plugin:card']);

        $response = self::$http->get("/plugin/{$plugin['key']}", ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame('singleplug', $body['key']);
    }

    public function testGetInactivePluginReturns404(): void
    {
        $plugin = $this->createPlugin(['key' => 'inactivesingle', 'name' => 'Inactive Single', 'active' => 0]);
        $token  = $this->authedToken(['plugin:card']);

        $response = self::$http->get("/plugin/{$plugin['key']}", ['headers' => $this->bearer($token)]);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testGetUnknownPluginReturns404(): void
    {
        $token = $this->authedToken(['plugin:card']);

        $response = self::$http->get('/plugin/no_such_plugin', ['headers' => $this->bearer($token)]);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testGetSinglePluginIncludesWatchedFlagForWatcher(): void
    {
        $plugin = $this->createPlugin(['key' => 'watchedplug', 'name' => 'Watched Plugin', 'active' => 1]);
        $user   = $this->createUser();
        $token  = $this->getAccessToken($user['username'], $user['plain_password'], ['plugin:card', 'user', 'plugins']);

        // Watch the plugin
        self::$http->post('/user/watchs', [
            'headers' => $this->bearer($token),
            'json'    => ['plugin_key' => $plugin['key']],
        ]);

        $response = self::$http->get("/plugin/{$plugin['key']}", ['headers' => $this->bearer($token)]);
        $body     = json_decode((string) $response->getBody(), true);

        $this->assertTrue($body['watched'], 'watched flag must be true for a watching user');
    }

    // -------------------------------------------------------------------------
    // GET /plugin/rss_new and /plugin/rss_updated (§6.4)
    // -------------------------------------------------------------------------

    public function testRssNewReturnsXmlWithoutAuth(): void
    {
        $this->createPlugin(['key' => 'rssplugin', 'name' => 'RSS Plugin', 'active' => 1]);

        $response = self::$http->get('/plugin/rss_new');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('<?xml', (string) $response->getBody());
    }

    public function testRssUpdatedReturnsXmlWithoutAuth(): void
    {
        $response = self::$http->get('/plugin/rss_updated');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('<?xml', (string) $response->getBody());
    }
}
