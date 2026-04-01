<?php

declare(strict_types=1);

namespace Tests\Functional\Plugin;

use Tests\Functional\FunctionalTestCase;

/**
 * Functional tests for GET /plugin/:key/download (§9).
 */
class DownloadTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $pdo = $this->db();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'plugin', 'plugin_download',
            'user', 'sessions', 'access_tokens', 'access_tokens_scopes',
            'refresh_tokens', 'sessions_scopes',
        ] as $t) {
            $pdo->exec("TRUNCATE TABLE `{$t}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function testDownloadIncrementsDownloadCount(): void
    {
        $plugin = $this->createPlugin([
            'key'            => 'dlplugin',
            'name'           => 'Download Plugin',
            'active'         => 1,
            'download_count' => 5,
            'download_url'   => 'http://example.com/plugin.zip',
        ]);

        self::$http->get("/plugin/{$plugin['key']}/download", [
            'headers' => ['Accept' => 'application/json'],
        ]);

        $stmt = $this->db()->prepare('SELECT download_count FROM `plugin` WHERE id = ?');
        $stmt->execute([$plugin['id']]);
        $this->assertSame(6, (int) $stmt->fetchColumn());
    }

    public function testDownloadCreatesPluginDownloadRecord(): void
    {
        $plugin = $this->createPlugin([
            'key'          => 'dlrecordplugin',
            'name'         => 'DL Record Plugin',
            'active'       => 1,
            'download_url' => 'http://example.com/plugin.zip',
        ]);

        self::$http->get("/plugin/{$plugin['key']}/download", [
            'headers' => ['Accept' => 'application/json'],
        ]);

        $stmt = $this->db()->prepare('SELECT COUNT(*) FROM `plugin_download` WHERE plugin_id = ?');
        $stmt->execute([$plugin['id']]);
        $this->assertSame(1, (int) $stmt->fetchColumn());
    }

    public function testDownloadWithJsonAcceptReturns200WithoutRedirect(): void
    {
        $plugin = $this->createPlugin([
            'key'          => 'jsondownload',
            'name'         => 'JSON Download Plugin',
            'active'       => 1,
            'download_url' => 'http://example.com/plugin.zip',
        ]);

        $response = self::$http->get("/plugin/{$plugin['key']}/download", [
            'headers' => ['Accept' => 'application/json'],
        ]);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testDownloadWithoutJsonAcceptRedirectsWith301(): void
    {
        $plugin = $this->createPlugin([
            'key'          => 'redirectdownload',
            'name'         => 'Redirect Download Plugin',
            'active'       => 1,
            'download_url' => 'http://example.com/plugin.zip',
        ]);

        $response = self::$http->get("/plugin/{$plugin['key']}/download");

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('http://example.com/plugin.zip', $response->getHeaderLine('Location'));
    }
}
