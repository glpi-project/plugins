<?php

declare(strict_types=1);

namespace Tests\Functional\User;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for POST /user/sendpasswordresetlink and PUT /user/password (§5).
 */
class PasswordResetTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['user', 'user_resetpassword_token', 'sessions', 'access_tokens', 'access_tokens_scopes', 'refresh_tokens', 'sessions_scopes'] as $t) {
            $pdo->exec("TRUNCATE TABLE `{$t}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    // -------------------------------------------------------------------------
    // Send reset link (§5.1)
    // -------------------------------------------------------------------------

    public function testKnownEmailCreatesResetTokenAndReturns200(): void
    {
        $user = $this->createUser();

        $response = self::$http->post('/user/sendpasswordresetlink', [
            'json' => ['email' => $user['email']],
        ]);

        $this->assertSame(200, $response->getStatusCode());

        $stmt = $this->db()->prepare('SELECT COUNT(*) FROM `user_resetpassword_token` WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        $this->assertSame(1, (int) $stmt->fetchColumn(), 'A reset token must be created');
    }

    public function testUnknownEmailReturns404(): void
    {
        $response = self::$http->post('/user/sendpasswordresetlink', [
            'json' => ['email' => 'nobody@example.com'],
        ]);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testMissingEmailFieldReturns400(): void
    {
        $response = self::$http->post('/user/sendpasswordresetlink', [
            'json' => [],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Reset password (§5.2)
    // -------------------------------------------------------------------------

    public function testValidTokenUpdatesPasswordAndDeletesToken(): void
    {
        $user = $this->createUser();
        $token = 'resettoken123abc';
        $this->db()->prepare(
            "INSERT INTO `user_resetpassword_token` (`token`, `user_id`) VALUES (?, ?)"
        )->execute([$token, $user['id']]);

        $response = self::$http->put('/user/password', [
            'json' => ['token' => $token, 'password' => 'NewPassword1'],
        ]);

        $this->assertSame(200, $response->getStatusCode());

        // Password hash updated
        $stmt = $this->db()->prepare('SELECT password FROM `user` WHERE id = ?');
        $stmt->execute([$user['id']]);
        $this->assertTrue(password_verify('NewPassword1', $stmt->fetchColumn()));

        // Reset tokens deleted
        $stmt = $this->db()->prepare('SELECT COUNT(*) FROM `user_resetpassword_token` WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        $this->assertSame(0, (int) $stmt->fetchColumn(), 'Reset tokens must be deleted after use');
    }

    public function testInvalidTokenReturns400(): void
    {
        $response = self::$http->put('/user/password', [
            'json' => ['token' => 'completely_wrong_token', 'password' => 'NewPassword1'],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testMissingPasswordReturns400(): void
    {
        $user = $this->createUser();
        $token = 'resettoken456xyz';
        $this->db()->prepare(
            "INSERT INTO `user_resetpassword_token` (`token`, `user_id`) VALUES (?, ?)"
        )->execute([$token, $user['id']]);

        $response = self::$http->put('/user/password', [
            'json' => ['token' => $token],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }
}
