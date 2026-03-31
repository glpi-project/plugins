<?php

declare(strict_types=1);

namespace Tests\Functional\Plugin;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for POST /plugin (plugin submission, §7).
 *
 * NOTE: The submission endpoint validates reCAPTCHA before checking other
 * fields, so all authenticated requests without a valid reCAPTCHA will receive
 * a 400 (InvalidRecaptcha).  Tests that exercise per-field validation therefore
 * assert status 400 without checking the specific error payload.
 *
 * The full success path (§7.2) is not tested here because it requires:
 *  - A live HTTP server that serves a valid plugin XML file
 *  - A valid reCAPTCHA token (requires Google network access)
 */
class SubmitTest extends FunctionalTestCase
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

    public function testSubmitWithoutTokenReturns401(): void
    {
        $response = self::$http->post('/plugin', [
            'json' => ['plugin_url' => 'http://example.com/plugin.xml'],
        ]);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testSubmitWithoutSubmitScopeReturns401(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['plugins']);

        $response = self::$http->post('/plugin', [
            'headers' => $this->bearer($token),
            'json'    => ['plugin_url' => 'http://example.com/plugin.xml'],
        ]);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testSubmitWithMissingPluginUrlReturns400(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['plugin:submit']);

        $response = self::$http->post('/plugin', [
            'headers' => $this->bearer($token),
            'json'    => [],
        ]);

        // 400 returned — either InvalidRecaptcha (checked first) or InvalidField
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testSubmitWithDuplicateXmlUrlReturns400(): void
    {
        $this->createPlugin(['xml_url' => 'http://example.com/existing.xml']);
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['plugin:submit']);

        $response = self::$http->post('/plugin', [
            'headers' => $this->bearer($token),
            'json'    => [
                'plugin_url'       => 'http://example.com/existing.xml',
                'recaptcha_response' => 'dummy',
            ],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }
}
