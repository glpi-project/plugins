<?php

declare(strict_types=1);

namespace Tests\Functional\User;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for POST /user/search (§17).
 */
class UserSearchTest extends FunctionalTestCase
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

    public function testSearchMatchesOnUsername(): void
    {
        $this->createUser(['username' => 'findme', 'email' => 'findme@example.com', 'realname' => 'Find Me']);
        $searcher = $this->createUser(['username' => 'searcher', 'email' => 'searcher@example.com']);
        $token    = $this->getAccessToken($searcher['username'], $searcher['plain_password'], ['users:search']);

        $response = self::$http->post('/user/search', [
            'headers' => $this->bearer($token),
            'json'    => ['search' => 'findme'],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $usernames = array_column($body, 'username');
        $this->assertContains('findme', $usernames);
    }

    public function testSearchMatchesOnRealname(): void
    {
        $this->createUser(['username' => 'jsmith', 'email' => 'jsmith@example.com', 'realname' => 'John Smith']);
        $searcher = $this->createUser(['username' => 'searcher2', 'email' => 'searcher2@example.com']);
        $token    = $this->getAccessToken($searcher['username'], $searcher['plain_password'], ['users:search']);

        $response = self::$http->post('/user/search', [
            'headers' => $this->bearer($token),
            'json'    => ['search' => 'John'],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $usernames = array_column($body, 'username');
        $this->assertContains('jsmith', $usernames);
    }

    public function testResultsContainOnlyUsernameAndRealname(): void
    {
        $this->createUser(['username' => 'checkfields', 'email' => 'checkfields@example.com']);
        $searcher = $this->createUser(['username' => 'searcher3', 'email' => 'searcher3@example.com']);
        $token    = $this->getAccessToken($searcher['username'], $searcher['plain_password'], ['users:search']);

        $response = self::$http->post('/user/search', [
            'headers' => $this->bearer($token),
            'json'    => ['search' => 'checkfields'],
        ]);

        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body);
        $result = $body[0];
        $this->assertArrayHasKey('username', $result);
        $this->assertArrayHasKey('realname', $result);
        $this->assertArrayNotHasKey('password', $result);
        $this->assertArrayNotHasKey('email', $result);
    }

    public function testMissingSearchFieldReturns400(): void
    {
        $user  = $this->createUser();
        $token = $this->getAccessToken($user['username'], $user['plain_password'], ['users:search']);

        $response = self::$http->post('/user/search', [
            'headers' => $this->bearer($token),
            'json'    => [],
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }
}
