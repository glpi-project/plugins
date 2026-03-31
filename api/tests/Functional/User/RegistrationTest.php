<?php

declare(strict_types=1);

namespace Tests\Functional\User;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for POST /user (user registration).
 *
 * The endpoint:
 *  - Validates the JSON body
 *  - Creates a user with active = false
 *  - Creates a ValidationToken
 *  - Tries to send a confirmation email (uses 'mail' transport in test config;
 *    PHP mail() returns false silently in CI but does not throw)
 */
class RegistrationTest extends FunctionalTestCase
{
    // -------------------------------------------------------------------------
    // Success
    // -------------------------------------------------------------------------

    public function testRegistrationReturns200(): void
    {
        $response = self::$http->post('/user', [
            'json' => [
                'username' => 'newuser',
                'email'    => 'newuser@example.com',
                'password' => 'Password1',
            ],
        ]);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testRegistrationCreatesInactiveUser(): void
    {
        self::$http->post('/user', [
            'json' => [
                'username' => 'newuser',
                'email'    => 'newuser@example.com',
                'password' => 'Password1',
            ],
        ]);

        $stmt = $this->db()->prepare('SELECT active FROM `user` WHERE username = ?');
        $stmt->execute(['newuser']);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotFalse($row, 'User must be created in the database');
        $this->assertSame(0, (int) $row['active'], 'Newly registered user must be inactive');
    }

    public function testRegistrationCreatesValidationToken(): void
    {
        self::$http->post('/user', [
            'json' => [
                'username' => 'newuser',
                'email'    => 'newuser@example.com',
                'password' => 'Password1',
            ],
        ]);

        $stmt = $this->db()->prepare(
            'SELECT t.token
               FROM user_validation_token t
               JOIN `user` u ON u.id = t.user_id
              WHERE u.username = ?'
        );
        $stmt->execute(['newuser']);
        $token = $stmt->fetchColumn();

        $this->assertNotFalse($token, 'A validation token must be created for the new user');
        $this->assertNotEmpty($token);
    }

    // -------------------------------------------------------------------------
    // Field validation failures
    // -------------------------------------------------------------------------

    public function testMissingUsernameReturns400(): void
    {
        $response = self::$http->post('/user', [
            'json' => ['email' => 'test@example.com', 'password' => 'Password1'],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testUsernameTooShortReturns400(): void
    {
        $response = self::$http->post('/user', [
            'json' => ['username' => 'ab', 'email' => 'test@example.com', 'password' => 'Password1'],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testUsernameWithSpecialCharactersReturns400(): void
    {
        $response = self::$http->post('/user', [
            'json' => ['username' => 'bad!user', 'email' => 'test@example.com', 'password' => 'Password1'],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testDuplicateUsernameReturns400(): void
    {
        $this->createUser(['username' => 'taken', 'email' => 'taken@example.com']);

        $response = self::$http->post('/user', [
            'json' => ['username' => 'taken', 'email' => 'other@example.com', 'password' => 'Password1'],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testInvalidEmailReturns400(): void
    {
        $response = self::$http->post('/user', [
            'json' => ['username' => 'newuser', 'email' => 'not-an-email', 'password' => 'Password1'],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testDuplicateEmailReturns400(): void
    {
        $this->createUser(['username' => 'user1', 'email' => 'shared@example.com']);

        $response = self::$http->post('/user', [
            'json' => ['username' => 'user2', 'email' => 'shared@example.com', 'password' => 'Password1'],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testPasswordTooShortReturns400(): void
    {
        $response = self::$http->post('/user', [
            'json' => ['username' => 'newuser', 'email' => 'test@example.com', 'password' => '123'],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }
}
