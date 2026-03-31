<?php

declare(strict_types=1);

namespace Tests\Functional\User;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for user app (OAuth client) endpoints (§19).
 *
 * POST   /user/apps
 * GET    /user/apps
 * GET    /user/apps/:id
 * PUT    /user/apps/:id
 * DELETE /user/apps/:id
 */
class AppsTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['user', 'sessions', 'access_tokens', 'access_tokens_scopes', 'refresh_tokens', 'sessions_scopes'] as $t) {
            $pdo->exec("TRUNCATE TABLE `{$t}`");
        }
        // Remove user-owned apps (keep webapp/glpidefault which have no user_id)
        $pdo->exec("DELETE FROM `apps` WHERE `user_id` IS NOT NULL");
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    // -------------------------------------------------------------------------
    // POST /user/apps (§19.1)
    // -------------------------------------------------------------------------

    public function testCreateAppReturns200(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'user:apps']);

        $response = self::$http->post('/user/apps', [
            'headers' => $this->bearer($token),
            'json'    => ['name' => 'My Test App'],
        ]);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testCreatedAppHasRandomClientIdAndSecret(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'user:apps']);

        self::$http->post('/user/apps', [
            'headers' => $this->bearer($token),
            'json'    => ['name' => 'SecretApp'],
        ]);

        $stmt = $this->db()->prepare(
            "SELECT id, secret FROM `apps` WHERE user_id = ? AND name = 'SecretApp'"
        );
        $stmt->execute([$user['id']]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($row);
        $this->assertNotEmpty($row['id']);
        $this->assertNotEmpty($row['secret']);
    }

    public function testDuplicateAppNameReturns400(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'user:apps']);

        self::$http->post('/user/apps', ['headers' => $this->bearer($token), 'json' => ['name' => 'DupApp']]);
        $response = self::$http->post('/user/apps', ['headers' => $this->bearer($token), 'json' => ['name' => 'DupApp']]);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testInvalidAppNameReturns400(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'user:apps']);

        $response = self::$http->post('/user/apps', [
            'headers' => $this->bearer($token),
            'json'    => ['name' => 'ab'],   // too short (< 4 chars)
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // GET /user/apps and GET /user/apps/:id (§19.2)
    // -------------------------------------------------------------------------

    public function testListAppsReturnsOnlyOwnApps(): void
    {
        $user  = $this->createUser();
        $other = $this->createUser(['username' => 'otheruser', 'email' => 'other@example.com']);
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'user:apps']);

        self::$http->post('/user/apps', ['headers' => $this->bearer($token), 'json' => ['name' => 'OwnApp']]);

        $response = self::$http->get('/user/apps', ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        foreach ($body as $app) {
            $stmt = $this->db()->prepare('SELECT user_id FROM `apps` WHERE id = ?');
            $stmt->execute([$app['id']]);
            $this->assertSame($user['id'], (int) $stmt->fetchColumn(), 'Must only return own apps');
        }
    }

    public function testGetSingleAppReturns200(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'user:apps']);

        self::$http->post('/user/apps', ['headers' => $this->bearer($token), 'json' => ['name' => 'SingleApp']]);
        $stmt = $this->db()->prepare("SELECT id FROM `apps` WHERE name = 'SingleApp'");
        $stmt->execute();
        $id = $stmt->fetchColumn();

        $response = self::$http->get("/user/apps/{$id}", ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testGetUnknownAppReturns404(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'user:apps']);

        $response = self::$http->get('/user/apps/99999', ['headers' => $this->bearer($token)]);

        $this->assertSame(404, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // PUT /user/apps/:id (§19.3)
    // -------------------------------------------------------------------------

    public function testUpdateAppPersistsChanges(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'user:apps']);

        self::$http->post('/user/apps', ['headers' => $this->bearer($token), 'json' => ['name' => 'UpdateMe']]);
        $stmt = $this->db()->prepare("SELECT id FROM `apps` WHERE name = 'UpdateMe'");
        $stmt->execute();
        $id = $stmt->fetchColumn();

        $response = self::$http->put("/user/apps/{$id}", [
            'headers' => $this->bearer($token),
            'json'    => ['name' => 'UpdatedName'],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $stmt = $this->db()->prepare("SELECT name FROM `apps` WHERE id = ?");
        $stmt->execute([$id]);
        $this->assertSame('UpdatedName', $stmt->fetchColumn());
    }

    // -------------------------------------------------------------------------
    // DELETE /user/apps/:id (§19.4)
    // -------------------------------------------------------------------------

    public function testDeleteAppRemovesRecord(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user', 'user:apps']);

        self::$http->post('/user/apps', ['headers' => $this->bearer($token), 'json' => ['name' => 'DeleteMe']]);
        $stmt = $this->db()->prepare("SELECT id FROM `apps` WHERE name = 'DeleteMe'");
        $stmt->execute();
        $id = $stmt->fetchColumn();

        $response = self::$http->delete("/user/apps/{$id}", ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
        $stmt = $this->db()->prepare("SELECT COUNT(*) FROM `apps` WHERE id = ?");
        $stmt->execute([$id]);
        $this->assertSame(0, (int) $stmt->fetchColumn());
    }
}
