<?php

declare(strict_types=1);

namespace Tests\Functional\Tags;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for tag endpoints (§13).
 *
 * GET /tags
 * GET /tags/top
 * GET /tags/:id
 * GET /tags/:id/plugin
 */
class TagsTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'tag', 'plugin', 'plugin_tags', 'plugin_description', 'plugin_stars',
            'user', 'sessions', 'access_tokens', 'access_tokens_scopes',
            'refresh_tokens', 'sessions_scopes',
        ] as $t) {
            $pdo->exec("TRUNCATE TABLE `{$t}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    private function authedToken(array $scopes): string
    {
        $user = $this->createUser();
        return $this->getAccessToken($user['username'], $user['plain_password'], $scopes);
    }

    // -------------------------------------------------------------------------
    // GET /tags and GET /tags/top (§13.1)
    // -------------------------------------------------------------------------

    public function testListTagsReturns200(): void
    {
        $this->createTag('network', 'Network', 'en');
        $token = $this->authedToken(['tags']);

        $response = self::$http->get('/tags', ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testTopTagsReturns200(): void
    {
        $this->createTag('inventory', 'Inventory', 'en');
        $token = $this->authedToken(['tags']);

        $response = self::$http->get('/tags/top', ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // GET /tags/:id (§13.2)
    // -------------------------------------------------------------------------

    public function testGetSingleTagReturns200(): void
    {
        $tag   = $this->createTag('security', 'Security', 'en');
        $token = $this->authedToken(['tag']);

        $response = self::$http->get("/tags/{$tag['key']}", ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame('security', $body['key']);
    }

    public function testGetUnknownTagReturns404(): void
    {
        $token = $this->authedToken(['tag']);

        $response = self::$http->get('/tags/no_such_tag', ['headers' => $this->bearer($token)]);

        $this->assertSame(404, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // GET /tags/:id/plugin (§13.3)
    // -------------------------------------------------------------------------

    public function testTagPluginsReturnsPluginsWithThatTag(): void
    {
        $tag    = $this->createTag('reporting', 'Reporting', 'en');
        $plugin = $this->createPlugin(['key' => 'taggedplug', 'name' => 'Tagged Plugin', 'active' => 1]);
        $this->db()->prepare("INSERT INTO `plugin_tags` (`plugin_id`, `tag_id`) VALUES (?, ?)")
                   ->execute([$plugin['id'], $tag['id']]);
        $token  = $this->authedToken(['tag', 'plugins']);

        $response = self::$http->get("/tags/{$tag['key']}/plugin", ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $keys = array_column($body, 'key');
        $this->assertContains('taggedplug', $keys);
    }
}
