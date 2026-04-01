<?php

declare(strict_types=1);

namespace Tests\Functional\User;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for user plugin watch endpoints (§16).
 *
 * POST  /user/watchs
 * DELETE /user/watchs/:key
 * GET   /user/watchs
 */
class WatchesTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'user', 'plugin', 'user_plugin_watch',
            'sessions', 'access_tokens', 'access_tokens_scopes',
            'refresh_tokens', 'sessions_scopes',
        ] as $t) {
            $pdo->exec("TRUNCATE TABLE `{$t}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    // -------------------------------------------------------------------------
    // POST /user/watchs (§16.1)
    // -------------------------------------------------------------------------

    public function testAddWatchCreatesRecord(): void
    {
        $user   = $this->createUser();
        $plugin = $this->createPlugin();
        $token  = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'plugins']);

        $response = self::$http->post('/user/watchs', [
            'headers' => $this->bearer($token),
            'json'    => ['plugin_key' => $plugin['key']],
        ]);

        $this->assertSame(200, $response->getStatusCode());

        $stmt = $this->db()->prepare(
            'SELECT COUNT(*) FROM `user_plugin_watch` WHERE user_id = ? AND plugin_id = ?'
        );
        $stmt->execute([$user['id'], $plugin['id']]);
        $this->assertSame(1, (int) $stmt->fetchColumn());
    }

    public function testAddWatchForUnknownPluginReturns404(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'plugins']);

        $response = self::$http->post('/user/watchs', [
            'headers' => $this->bearer($token),
            'json'    => ['plugin_key' => 'no_such_plugin'],
        ]);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testAddWatchTwiceReturns400(): void
    {
        $user   = $this->createUser();
        $plugin = $this->createPlugin();
        $token  = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'plugins']);

        self::$http->post('/user/watchs', [
            'headers' => $this->bearer($token),
            'json'    => ['plugin_key' => $plugin['key']],
        ]);
        $response = self::$http->post('/user/watchs', [
            'headers' => $this->bearer($token),
            'json'    => ['plugin_key' => $plugin['key']],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // DELETE /user/watchs/:key (§16.2)
    // -------------------------------------------------------------------------

    public function testRemoveWatchDeletesRecord(): void
    {
        $user   = $this->createUser();
        $plugin = $this->createPlugin();
        $token  = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'plugins']);

        // Watch first
        self::$http->post('/user/watchs', [
            'headers' => $this->bearer($token),
            'json'    => ['plugin_key' => $plugin['key']],
        ]);

        $response = self::$http->delete('/user/watchs/' . $plugin['key'], [
            'headers' => $this->bearer($token),
        ]);

        $this->assertSame(200, $response->getStatusCode());

        $stmt = $this->db()->prepare(
            'SELECT COUNT(*) FROM `user_plugin_watch` WHERE user_id = ? AND plugin_id = ?'
        );
        $stmt->execute([$user['id'], $plugin['id']]);
        $this->assertSame(0, (int) $stmt->fetchColumn());
    }

    public function testRemoveNonExistingWatchReturns404(): void
    {
        $user   = $this->createUser();
        $plugin = $this->createPlugin();
        $token  = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'plugins']);

        $response = self::$http->delete('/user/watchs/' . $plugin['key'], [
            'headers' => $this->bearer($token),
        ]);

        $this->assertSame(404, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // GET /user/watchs (§16.3)
    // -------------------------------------------------------------------------

    public function testGetWatchesReturnsPluginKeys(): void
    {
        $user    = $this->createUser();
        $plugin1 = $this->createPlugin(['key' => 'watchplugin1', 'name' => 'Watch Plugin 1']);
        $plugin2 = $this->createPlugin(['key' => 'watchplugin2', 'name' => 'Watch Plugin 2']);
        $token   = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'plugins']);

        self::$http->post('/user/watchs', ['headers' => $this->bearer($token), 'json' => ['plugin_key' => $plugin1['key']]]);
        self::$http->post('/user/watchs', ['headers' => $this->bearer($token), 'json' => ['plugin_key' => $plugin2['key']]]);

        $response = self::$http->get('/user/watchs', ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertContains($plugin1['key'], $body);
        $this->assertContains($plugin2['key'], $body);
    }
}
