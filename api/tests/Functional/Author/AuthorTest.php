<?php

declare(strict_types=1);

namespace Tests\Functional\Author;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for author endpoints (§12).
 *
 * GET /author
 * GET /author/top
 * GET /author/:id
 * GET /author/:id/plugin
 */
class AuthorTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'author', 'plugin', 'plugin_author', 'plugin_description', 'plugin_stars',
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
    // GET /author (§12.1) — only authors with plugins
    // -------------------------------------------------------------------------

    public function testListAuthorsReturns200(): void
    {
        $token = $this->authedToken(['authors']);

        $response = self::$http->get('/author', ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testListAuthorsExcludesAuthorsWithNoPlugins(): void
    {
        $this->createAuthor('NoPluginsAuthor');
        $token = $this->authedToken(['authors']);

        $response = self::$http->get('/author', ['headers' => $this->bearer($token)]);

        $body    = json_decode((string) $response->getBody(), true);
        $names   = array_column($body, 'name');
        $this->assertNotContains('NoPluginsAuthor', $names);
    }

    // -------------------------------------------------------------------------
    // GET /author/:id (§12.2)
    // -------------------------------------------------------------------------

    public function testGetSingleAuthorReturns200(): void
    {
        $author = $this->createAuthor('Solo Author');
        $token  = $this->authedToken(['author']);

        $response = self::$http->get("/author/{$author['id']}", ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame('Solo Author', $body['name']);
    }

    public function testGetAuthorLinkedToUserIncludesUsernameAndGravatar(): void
    {
        $user   = $this->createUser(['username' => 'linkeduser', 'email' => 'linked@example.com']);
        $author = $this->createAuthor('Linked Author');
        // link user to author
        $this->db()->prepare("UPDATE `user` SET author_id = ? WHERE id = ?")->execute([$author['id'], $user['id']]);
        $this->db()->prepare("UPDATE `author` SET username = ? WHERE id = ?")->execute([$user['username'], $author['id']]);
        $token  = $this->authedToken(['author']);

        $response = self::$http->get("/author/{$author['id']}", ['headers' => $this->bearer($token)]);

        $body = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('username', $body);
        $this->assertArrayHasKey('gravatar', $body);
    }

    public function testGetUnknownAuthorReturns404(): void
    {
        $token = $this->authedToken(['author']);

        $response = self::$http->get('/author/99999', ['headers' => $this->bearer($token)]);

        $this->assertSame(404, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // GET /author/:id/plugin (§12.3)
    // -------------------------------------------------------------------------

    public function testGetAuthorPluginsReturnsOnlyActivePlugins(): void
    {
        $author      = $this->createAuthor('Plugin Author');
        $activePlugin   = $this->createPlugin(['key' => 'authoractive',   'name' => 'Author Active',   'active' => 1]);
        $inactivePlugin = $this->createPlugin(['key' => 'authorinactive', 'name' => 'Author Inactive', 'active' => 0]);

        $this->db()->prepare("INSERT INTO `plugin_author` (`plugin_id`, `author_id`) VALUES (?, ?)")
                   ->execute([$activePlugin['id'], $author['id']]);
        $this->db()->prepare("INSERT INTO `plugin_author` (`plugin_id`, `author_id`) VALUES (?, ?)")
                   ->execute([$inactivePlugin['id'], $author['id']]);

        $token = $this->authedToken(['author', 'plugins']);

        $response = self::$http->get("/author/{$author['id']}/plugin", ['headers' => $this->bearer($token)]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $keys = array_column($body, 'key');
        $this->assertContains('authoractive', $keys);
        $this->assertNotContains('authorinactive', $keys);
    }
}
