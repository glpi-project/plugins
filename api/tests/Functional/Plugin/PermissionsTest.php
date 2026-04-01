<?php

declare(strict_types=1);

namespace Tests\Functional\Plugin;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for plugin permission endpoints (§10).
 *
 * GET    /plugin/:key/permissions
 * POST   /plugin/:key/permissions
 * DELETE /plugin/:key/permissions/:username
 * PATCH  /plugin/:key/permissions/:username
 */
class PermissionsTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'plugin', 'plugin_permission',
            'user', 'sessions', 'access_tokens', 'access_tokens_scopes',
            'refresh_tokens', 'sessions_scopes',
        ] as $t) {
            $pdo->exec("TRUNCATE TABLE `{$t}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    // -------------------------------------------------------------------------
    // GET /plugin/:key/permissions (§10.1)
    // -------------------------------------------------------------------------

    public function testAdminCanViewPermissions(): void
    {
        $admin  = $this->createUser();
        $plugin = $this->createPlugin(['key' => 'permplug', 'name' => 'Perm Plugin', 'active' => 1]);
        $this->grantPermission($plugin['id'], $admin['id'], true);
        $token  = $this->getAccessToken($admin['username'], $admin['plain_password'], ['user', 'plugin:card']);

        $response = self::$http->get("/plugin/{$plugin['key']}/permissions", [
            'headers' => $this->bearer($token),
        ]);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testNonAdminCannotViewPermissions(): void
    {
        $user   = $this->createUser();
        $plugin = $this->createPlugin(['key' => 'permplug2', 'name' => 'Perm Plugin 2', 'active' => 1]);
        $this->grantPermission($plugin['id'], $user['id'], false);
        $token  = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'plugin:card']);

        $response = self::$http->get("/plugin/{$plugin['key']}/permissions", [
            'headers' => $this->bearer($token),
        ]);

        $this->assertSame(401, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // POST /plugin/:key/permissions (§10.2)
    // -------------------------------------------------------------------------

    public function testAdminCanAddPermission(): void
    {
        $admin  = $this->createUser(['username' => 'adminuser', 'email' => 'admin@example.com']);
        $target = $this->createUser(['username' => 'targetuser', 'email' => 'target@example.com']);
        $plugin = $this->createPlugin(['key' => 'addpermplug', 'name' => 'Add Perm Plugin', 'active' => 1]);
        $this->grantPermission($plugin['id'], $admin['id'], true);
        $token  = $this->getAccessToken($admin['username'], $admin['plain_password'], ['user', 'plugin:card']);

        $response = self::$http->post("/plugin/{$plugin['key']}/permissions", [
            'headers' => $this->bearer($token),
            'json'    => ['username' => $target['username']],
        ]);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testAddPermissionForNonExistentUserReturns404(): void
    {
        $admin  = $this->createUser();
        $plugin = $this->createPlugin(['key' => 'addperm404', 'name' => 'Add Perm 404', 'active' => 1]);
        $this->grantPermission($plugin['id'], $admin['id'], true);
        $token  = $this->getAccessToken($admin['username'], $admin['plain_password'], ['user', 'plugin:card']);

        $response = self::$http->post("/plugin/{$plugin['key']}/permissions", [
            'headers' => $this->bearer($token),
            'json'    => ['username' => 'nobody_here'],
        ]);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testAddPermissionAlreadyExistsReturns400(): void
    {
        $admin  = $this->createUser(['username' => 'admindup', 'email' => 'admindup@example.com']);
        $target = $this->createUser(['username' => 'targetdup', 'email' => 'targetdup@example.com']);
        $plugin = $this->createPlugin(['key' => 'addpermdup', 'name' => 'Add Perm Dup', 'active' => 1]);
        $this->grantPermission($plugin['id'], $admin['id'], true);
        $this->grantPermission($plugin['id'], $target['id'], false);
        $token  = $this->getAccessToken($admin['username'], $admin['plain_password'], ['user', 'plugin:card']);

        $response = self::$http->post("/plugin/{$plugin['key']}/permissions", [
            'headers' => $this->bearer($token),
            'json'    => ['username' => $target['username']],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testNonAdminCannotAddPermission(): void
    {
        $user   = $this->createUser();
        $target = $this->createUser(['username' => 'targetna', 'email' => 'targetna@example.com']);
        $plugin = $this->createPlugin(['key' => 'addpermna', 'name' => 'Add Perm NA', 'active' => 1]);
        $this->grantPermission($plugin['id'], $user['id'], false);
        $token  = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'plugin:card']);

        $response = self::$http->post("/plugin/{$plugin['key']}/permissions", [
            'headers' => $this->bearer($token),
            'json'    => ['username' => $target['username']],
        ]);

        $this->assertSame(401, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // DELETE /plugin/:key/permissions/:username (§10.3)
    // -------------------------------------------------------------------------

    public function testNonAdminCanRemoveOwnPermission(): void
    {
        $admin  = $this->createUser(['username' => 'admindel', 'email' => 'admindel@example.com']);
        $user   = $this->createUser(['username' => 'selfremove', 'email' => 'selfremove@example.com']);
        $plugin = $this->createPlugin(['key' => 'delpermplug', 'name' => 'Del Perm Plugin', 'active' => 1]);
        $this->grantPermission($plugin['id'], $admin['id'], true);
        $this->grantPermission($plugin['id'], $user['id'], false);
        $token  = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'plugin:card']);

        $response = self::$http->delete("/plugin/{$plugin['key']}/permissions/{$user['username']}", [
            'headers' => $this->bearer($token),
        ]);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testCannotDeleteAdminPermissionReturns401(): void
    {
        $admin  = $this->createUser(['username' => 'adminnd', 'email' => 'adminnd@example.com']);
        $other  = $this->createUser(['username' => 'otheradmin', 'email' => 'otheradmin@example.com']);
        $plugin = $this->createPlugin(['key' => 'nodeladminplug', 'name' => 'No Del Admin', 'active' => 1]);
        $this->grantPermission($plugin['id'], $admin['id'], true);
        $this->grantPermission($plugin['id'], $other['id'], true);
        $token  = $this->getAccessToken($admin['username'], $admin['plain_password'], ['user', 'plugin:card']);

        $response = self::$http->delete("/plugin/{$plugin['key']}/permissions/{$other['username']}", [
            'headers' => $this->bearer($token),
        ]);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testDeleteNonExistentPermissionReturns400(): void
    {
        $admin  = $this->createUser(['username' => 'adminnoperm', 'email' => 'adminnoperm@example.com']);
        $plugin = $this->createPlugin(['key' => 'delperm404', 'name' => 'Del Perm 404', 'active' => 1]);
        $this->grantPermission($plugin['id'], $admin['id'], true);
        $token  = $this->getAccessToken($admin['username'], $admin['plain_password'], ['user', 'plugin:card']);

        $response = self::$http->delete("/plugin/{$plugin['key']}/permissions/nobody", [
            'headers' => $this->bearer($token),
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // PATCH /plugin/:key/permissions/:username (§10.4)
    // -------------------------------------------------------------------------

    public function testAdminCanModifyPermissionFlag(): void
    {
        $admin  = $this->createUser(['username' => 'adminmod', 'email' => 'adminmod@example.com']);
        $target = $this->createUser(['username' => 'targetmod', 'email' => 'targetmod@example.com']);
        $plugin = $this->createPlugin(['key' => 'modpermplug', 'name' => 'Mod Perm Plugin', 'active' => 1]);
        $this->grantPermission($plugin['id'], $admin['id'], true);
        $this->grantPermission($plugin['id'], $target['id'], false);
        $token  = $this->getAccessToken($admin['username'], $admin['plain_password'], ['user', 'plugin:card']);

        $response = self::$http->patch("/plugin/{$plugin['key']}/permissions/{$target['username']}", [
            'headers' => $this->bearer($token),
            'json'    => ['right' => 'allowed_refresh_xml', 'set' => true],
        ]);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testInvalidRightValueReturns400(): void
    {
        $admin  = $this->createUser(['username' => 'adminbadright', 'email' => 'adminbadright@example.com']);
        $target = $this->createUser(['username' => 'targetbadright', 'email' => 'targetbadright@example.com']);
        $plugin = $this->createPlugin(['key' => 'badrightplug', 'name' => 'Bad Right Plugin', 'active' => 1]);
        $this->grantPermission($plugin['id'], $admin['id'], true);
        $this->grantPermission($plugin['id'], $target['id'], false);
        $token  = $this->getAccessToken($admin['username'], $admin['plain_password'], ['user', 'plugin:card']);

        $response = self::$http->patch("/plugin/{$plugin['key']}/permissions/{$target['username']}", [
            'headers' => $this->bearer($token),
            'json'    => ['right' => 'nonexistent_right', 'set' => true],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testMissingSetFieldReturns400(): void
    {
        $admin  = $this->createUser(['username' => 'adminnoset', 'email' => 'adminnoset@example.com']);
        $target = $this->createUser(['username' => 'targetnoset', 'email' => 'targetnoset@example.com']);
        $plugin = $this->createPlugin(['key' => 'nosetplug', 'name' => 'No Set Plugin', 'active' => 1]);
        $this->grantPermission($plugin['id'], $admin['id'], true);
        $this->grantPermission($plugin['id'], $target['id'], false);
        $token  = $this->getAccessToken($admin['username'], $admin['plain_password'], ['user', 'plugin:card']);

        $response = self::$http->patch("/plugin/{$plugin['key']}/permissions/{$target['username']}", [
            'headers' => $this->bearer($token),
            'json'    => ['right' => 'allowed_refresh_xml'],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }
}
