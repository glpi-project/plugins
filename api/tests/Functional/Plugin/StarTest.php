<?php

declare(strict_types=1);

namespace Tests\Functional\Plugin;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for POST /plugin/star (§8).
 */
class StarTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'plugin', 'plugin_stars',
            'user', 'sessions', 'access_tokens', 'access_tokens_scopes',
            'refresh_tokens', 'sessions_scopes',
        ] as $t) {
            $pdo->exec("TRUNCATE TABLE `{$t}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function testStarPluginCreatesRecordAndReturnsNewAverage(): void
    {
        $plugin = $this->createPlugin(['key' => 'starplugin', 'name' => 'Star Plugin', 'active' => 1]);
        $user   = $this->createUser();
        $token  = $this->getAccessToken($user['username'], $user['plain_password'], ['plugin:star']);

        $response = self::$http->post('/plugin/star', [
            'headers' => $this->bearer($token),
            'json'    => ['plugin_id' => $plugin['id'], 'note' => 4],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('new_average', $body);
        $this->assertEquals(4.0, (float) $body['new_average'], '', 0.01);

        $stmt = $this->db()->prepare('SELECT COUNT(*) FROM `plugin_stars` WHERE plugin_id = ?');
        $stmt->execute([$plugin['id']]);
        $this->assertSame(1, (int) $stmt->fetchColumn());
    }

    public function testNonNumericPluginIdReturns400(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['plugin:star']);

        $response = self::$http->post('/plugin/star', [
            'headers' => $this->bearer($token),
            'json'    => ['plugin_id' => 'abc', 'note' => 3],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testNonExistentPluginReturns400(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['plugin:star']);

        $response = self::$http->post('/plugin/star', [
            'headers' => $this->bearer($token),
            'json'    => ['plugin_id' => 99999, 'note' => 3],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testMissingScopeReturns401(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['plugins']);

        $response = self::$http->post('/plugin/star', [
            'headers' => $this->bearer($token),
            'json'    => ['plugin_id' => 1, 'note' => 3],
        ]);

        $this->assertSame(401, $response->getStatusCode());
    }
}
