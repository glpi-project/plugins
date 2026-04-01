<?php

declare(strict_types=1);

namespace Tests\Functional\User;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for GET /user, PUT /user, POST /user/delete (§4).
 */
class ProfileTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['user', 'sessions', 'access_tokens', 'access_tokens_scopes', 'refresh_tokens', 'sessions_scopes'] as $t) {
            $pdo->exec("TRUNCATE TABLE `{$t}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    // -------------------------------------------------------------------------
    // View profile (§4.1)
    // -------------------------------------------------------------------------

    public function testViewProfileReturnsUserFields(): void
    {
        $user = $this->createUser(['realname' => 'Test User']);
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user']);

        $response = self::$http->get('/user', ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame($user['username'], $body['username']);
        $this->assertArrayHasKey('gravatar', $body);
    }

    public function testViewProfileWithoutTokenReturns401(): void
    {
        $response = self::$http->get('/user');

        $this->assertSame(401, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Edit profile (§4.2)
    // -------------------------------------------------------------------------

    public function testEditRealnameIsPersisted(): void
    {
        $user = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user']);

        self::$http->put('/user', [
            'headers' => $this->bearer($token),
            'json'    => ['realname' => 'Updated Name'],
        ]);

        $stmt = $this->db()->prepare('SELECT realname FROM `user` WHERE username = ?');
        $stmt->execute([$user['username']]);
        $this->assertSame('Updated Name', $stmt->fetchColumn());
    }

    public function testEditPasswordIsHashedBeforeSaving(): void
    {
        $user = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user']);

        self::$http->put('/user', [
            'headers' => $this->bearer($token),
            'json'    => ['password' => 'NewPassword1'],
        ]);

        $stmt = $this->db()->prepare('SELECT password FROM `user` WHERE username = ?');
        $stmt->execute([$user['username']]);
        $hash = $stmt->fetchColumn();

        $this->assertNotSame('NewPassword1', $hash, 'Password must not be stored in plain text');
        $this->assertTrue(password_verify('NewPassword1', $hash), 'Stored hash must match new password');
    }

    public function testEditProfileWithoutTokenReturns401(): void
    {
        $response = self::$http->put('/user', ['json' => ['realname' => 'Nobody']]);

        $this->assertSame(401, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Delete account (§4.3)
    // -------------------------------------------------------------------------

    public function testDeleteAccountWithCorrectPasswordSucceeds(): void
    {
        $user = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user']);

        $response = self::$http->post('/user/delete', [
            'headers' => $this->bearer($token),
            'json'    => ['password' => $user['plain_password']],
        ]);

        $this->assertSame(200, $response->getStatusCode());

        $stmt = $this->db()->prepare('SELECT COUNT(*) FROM `user` WHERE username = ?');
        $stmt->execute([$user['username']]);
        $this->assertSame(0, (int) $stmt->fetchColumn(), 'User record must be deleted');
    }

    public function testDeleteAccountWithWrongPasswordReturns401(): void
    {
        $user = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user']);

        $response = self::$http->post('/user/delete', [
            'headers' => $this->bearer($token),
            'json'    => ['password' => 'WrongPass1'],
        ]);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testDeleteAccountWithInvalidPasswordFormatReturns400(): void
    {
        $user = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['user']);

        $response = self::$http->post('/user/delete', [
            'headers' => $this->bearer($token),
            'json'    => ['password' => 'ab'],  // too short
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }
}
