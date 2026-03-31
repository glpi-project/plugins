<?php

declare(strict_types=1);

namespace Tests\Functional\User;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for GET /user/validatemail/:token (§3.3).
 */
class EmailValidationTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['user', 'user_validation_token', 'sessions', 'access_tokens', 'access_tokens_scopes', 'refresh_tokens', 'sessions_scopes'] as $t) {
            $pdo->exec("TRUNCATE TABLE `{$t}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function testValidTokenActivatesUserAndReturnsAccessToken(): void
    {
        $user = $this->createUser(['active' => 0]);
        $token = 'validtoken123';
        $this->db()->prepare(
            "INSERT INTO `user_validation_token` (`token`, `user_id`) VALUES (?, ?)"
        )->execute([$token, $user['id']]);

        $response = self::$http->get("/user/validatemail/{$token}");

        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('access_token', $body);
        $this->assertNotEmpty($body['access_token']);
    }

    public function testValidTokenActivatesUserInDatabase(): void
    {
        $user = $this->createUser(['active' => 0]);
        $token = 'activatetoken456';
        $this->db()->prepare(
            "INSERT INTO `user_validation_token` (`token`, `user_id`) VALUES (?, ?)"
        )->execute([$token, $user['id']]);

        self::$http->get("/user/validatemail/{$token}");

        $stmt = $this->db()->prepare('SELECT active FROM `user` WHERE id = ?');
        $stmt->execute([$user['id']]);
        $this->assertSame(1, (int) $stmt->fetchColumn(), 'User must be active after email validation');
    }

    public function testValidTokenIsDeletedAfterUse(): void
    {
        $user = $this->createUser(['active' => 0]);
        $token = 'deletetoken789';
        $this->db()->prepare(
            "INSERT INTO `user_validation_token` (`token`, `user_id`) VALUES (?, ?)"
        )->execute([$token, $user['id']]);

        self::$http->get("/user/validatemail/{$token}");

        $stmt = $this->db()->prepare('SELECT COUNT(*) FROM `user_validation_token` WHERE token = ?');
        $stmt->execute([$token]);
        $this->assertSame(0, (int) $stmt->fetchColumn(), 'Token must be deleted after use');
    }

    public function testInvalidTokenReturns400(): void
    {
        $response = self::$http->get('/user/validatemail/no_such_token');

        $this->assertSame(400, $response->getStatusCode());
    }
}
