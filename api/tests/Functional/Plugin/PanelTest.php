<?php

declare(strict_types=1);

namespace Tests\Functional\Plugin;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for the author panel endpoints (§11).
 *
 * GET  /panel/plugin/:key  — view card + statistics
 * POST /panel/plugin/:key  — update xml_url (XML-URL update tests are skipped:
 *                            they require a live HTTP endpoint to serve valid XML)
 */
class PanelTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'plugin', 'plugin_permission', 'plugin_description', 'plugin_version',
            'plugin_stars', 'plugin_screenshot', 'plugin_tags', 'plugin_author',
            'user', 'sessions', 'access_tokens', 'access_tokens_scopes',
            'refresh_tokens', 'sessions_scopes',
        ] as $t) {
            $pdo->exec("TRUNCATE TABLE `{$t}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    // -------------------------------------------------------------------------
    // GET /panel/plugin/:key (§11.1)
    // -------------------------------------------------------------------------

    public function testAdminUserCanViewPanel(): void
    {
        $admin  = $this->createUser();
        $plugin = $this->createPlugin(['key' => 'panelplug', 'name' => 'Panel Plugin', 'active' => 1]);
        $this->grantPermission($plugin['id'], $admin['id'], true);
        $token  = $this->getAccessToken($admin['username'], $admin['plain_password'], ['plugin:card', 'user']);

        $response = self::$http->get("/panel/plugin/{$plugin['key']}", [
            'headers' => $this->bearer($token),
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('card', $body);
        $this->assertArrayHasKey('statistics', $body);
    }

    public function testUserWithoutPermissionCannotViewPanel(): void
    {
        $user   = $this->createUser();
        $plugin = $this->createPlugin(['key' => 'panelnoperm', 'name' => 'Panel No Perm', 'active' => 1]);
        $token  = $this->getAccessToken($user['username'], $user['plain_password'], ['plugin:card', 'user']);

        $response = self::$http->get("/panel/plugin/{$plugin['key']}", [
            'headers' => $this->bearer($token),
        ]);

        $this->assertSame(401, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // POST /panel/plugin/:key (§11.2)
    // -------------------------------------------------------------------------

    public function testUpdateWithNonUrlStringReturns400(): void
    {
        $admin  = $this->createUser();
        $plugin = $this->createPlugin(['key' => 'paneledit', 'name' => 'Panel Edit', 'active' => 1]);
        $this->grantPermission($plugin['id'], $admin['id'], true);
        $token  = $this->getAccessToken($admin['username'], $admin['plain_password'], ['user', 'plugin:card']);

        $response = self::$http->post("/panel/plugin/{$plugin['key']}", [
            'headers' => $this->bearer($token),
            'json'    => ['xml_url' => 'not-a-url'],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testUpdateWithUnfetchableUrlReturns400(): void
    {
        $admin  = $this->createUser();
        $plugin = $this->createPlugin(['key' => 'panelunfetch', 'name' => 'Panel Unfetch', 'active' => 1]);
        $this->grantPermission($plugin['id'], $admin['id'], true);
        $token  = $this->getAccessToken($admin['username'], $admin['plain_password'], ['user', 'plugin:card']);

        $response = self::$http->post("/panel/plugin/{$plugin['key']}", [
            'headers' => $this->bearer($token),
            'json'    => ['xml_url' => 'http://localhost:19999/nonexistent.xml'],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }
}
