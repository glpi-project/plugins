<?php

declare(strict_types=1);

namespace Tests\Functional\Auth;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for POST /oauth/authorize.
 *
 * The authorize endpoint delegates to League\OAuth2\Server (v4) which reads
 * request parameters from $_POST (application/x-www-form-urlencoded), not
 * from the JSON body.  Guzzle's `form_params` option sets the correct
 * Content-Type automatically.
 */
class OAuthTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        // Truncate per-test mutable tables so each test starts with a clean slate.
        // apps and scopes are re-seeded by setUpBeforeClass; keep them intact.
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'user', 'sessions', 'access_tokens', 'access_tokens_scopes',
            'refresh_tokens', 'sessions_scopes',
            'user_validation_token', 'user_resetpassword_token',
        ] as $table) {
            $pdo->exec("TRUNCATE TABLE `{$table}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    // -------------------------------------------------------------------------
    // Password grant — success
    // -------------------------------------------------------------------------

    public function testPasswordGrantReturns200WithTokens(): void
    {
        $this->createUser();

        $response = self::$http->post('/oauth/authorize', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id'  => 'webapp',
                'username'   => 'testuser',
                'password'   => 'Password1',
                'scope'      => 'plugins user',
            ],
        ]);

        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('access_token', $body);
        $this->assertArrayHasKey('refresh_token', $body);
        $this->assertNotEmpty($body['access_token']);
        $this->assertNotEmpty($body['refresh_token']);
    }

    public function testPasswordGrantTokenIsPersistedInDatabase(): void
    {
        $this->createUser();
        $token = $this->getAccessToken('testuser', 'Password1');

        $this->assertNotNull($token, 'getAccessToken() must succeed');

        $stmt = $this->db()->prepare('SELECT id FROM access_tokens WHERE token = ?');
        $stmt->execute([$token]);
        $this->assertNotFalse($stmt->fetch(), 'Issued token must be stored in access_tokens');
    }

    public function testPasswordGrantCanLoginByEmail(): void
    {
        $this->createUser(['username' => 'byemail', 'email' => 'byemail@example.com']);

        $response = self::$http->post('/oauth/authorize', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id'  => 'webapp',
                'username'   => 'byemail@example.com',  // use email as login
                'password'   => 'Password1',
                'scope'      => 'plugins',
            ],
        ]);

        $this->assertSame(200, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Password grant — failures
    // -------------------------------------------------------------------------

    public function testWrongPasswordReturnsErrorStatus(): void
    {
        $this->createUser();

        $response = self::$http->post('/oauth/authorize', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id'  => 'webapp',
                'username'   => 'testuser',
                'password'   => 'WrongPassword',
                'scope'      => 'plugins',
            ],
        ]);

        $this->assertGreaterThanOrEqual(400, $response->getStatusCode());
    }

    public function testInactiveAccountReturnsErrorStatus(): void
    {
        $this->createUser(['active' => 0]);

        $response = self::$http->post('/oauth/authorize', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id'  => 'webapp',
                'username'   => 'testuser',
                'password'   => 'Password1',
                'scope'      => 'plugins',
            ],
        ]);

        $this->assertGreaterThanOrEqual(400, $response->getStatusCode());
    }

    public function testUnknownUsernameReturnsErrorStatus(): void
    {
        $response = self::$http->post('/oauth/authorize', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id'  => 'webapp',
                'username'   => 'nobody',
                'password'   => 'Password1',
                'scope'      => 'plugins',
            ],
        ]);

        $this->assertGreaterThanOrEqual(400, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Refresh token grant
    // -------------------------------------------------------------------------

    public function testRefreshTokenGrantIssuesNewTokens(): void
    {
        $this->createUser();

        $first = json_decode((string) self::$http->post('/oauth/authorize', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id'  => 'webapp',
                'username'   => 'testuser',
                'password'   => 'Password1',
                'scope'      => 'plugins',
            ],
        ])->getBody(), true);

        $response = self::$http->post('/oauth/authorize', [
            'form_params' => [
                'grant_type'    => 'refresh_token',
                'client_id'     => 'webapp',
                'refresh_token' => $first['refresh_token'],
            ],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $second = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('access_token', $second);
        $this->assertNotSame($first['access_token'], $second['access_token']);
    }

    public function testInvalidRefreshTokenReturnsErrorStatus(): void
    {
        $response = self::$http->post('/oauth/authorize', [
            'form_params' => [
                'grant_type'    => 'refresh_token',
                'client_id'     => 'webapp',
                'refresh_token' => 'completely_invalid_token',
            ],
        ]);

        $this->assertGreaterThanOrEqual(400, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Client credentials grant
    // -------------------------------------------------------------------------

    public function testClientCredentialsGrantReturnsToken(): void
    {
        $this->db()->exec(
            "INSERT INTO `apps` (`id`, `name`, `secret`) VALUES ('testapp0000000000001', 'Test App', 'appsecret')"
        );

        $response = self::$http->post('/oauth/authorize', [
            'form_params' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => 'testapp0000000000001',
                'client_secret' => 'appsecret',
                'scope'         => 'plugins',
            ],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('access_token', $body);
    }

    public function testClientCredentialsWithUnknownClientReturnsError(): void
    {
        $response = self::$http->post('/oauth/authorize', [
            'form_params' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => 'does_not_exist',
                'client_secret' => 'wrong',
                'scope'         => 'plugins',
            ],
        ]);

        $this->assertGreaterThanOrEqual(400, $response->getStatusCode());
    }
}
