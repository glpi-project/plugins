<?php

declare(strict_types=1);

namespace Tests\Functional;

/**
 * Functional tests for POST /message (§20).
 *
 * NOTE: The endpoint validates reCAPTCHA before checking required fields.
 * All authenticated requests without a valid reCAPTCHA will receive 400
 * (InvalidRecaptcha). Tests therefore assert status 400 without checking the
 * specific error payload for field-validation cases.
 */
class MessageTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'user', 'sessions', 'access_tokens', 'access_tokens_scopes',
            'refresh_tokens', 'sessions_scopes',
        ] as $t) {
            $pdo->exec("TRUNCATE TABLE `{$t}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function testMessageWithoutTokenReturns401(): void
    {
        $response = self::$http->post('/message', [
            'json' => [
                'firstname' => 'John',
                'lastname'  => 'Doe',
                'email'     => 'john@example.com',
                'subject'   => 'Hello',
                'message'   => 'Test message',
            ],
        ]);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testMessageWithoutMessageScopeReturns401(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['plugins']);

        $response = self::$http->post('/message', [
            'headers' => $this->bearer($token),
            'json'    => ['firstname' => 'John', 'email' => 'john@example.com'],
        ]);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testMessageWithScopeButNoRecaptchaReturns400(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['message']);

        $response = self::$http->post('/message', [
            'headers' => $this->bearer($token),
            'json'    => [
                'firstname' => 'John',
                'lastname'  => 'Doe',
                'email'     => 'john@example.com',
                'subject'   => 'Hello',
                'message'   => 'Test message body',
            ],
        ]);

        // 400: either InvalidRecaptcha (recaptcha_response missing/invalid) or
        // a field error — both are correct 400 responses
        $this->assertSame(400, $response->getStatusCode());
    }
}
