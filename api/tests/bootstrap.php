<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Provide a default config so Tool::getConfig() never loads the real config.php
API\Core\Tool::$config = [
    'default_number_of_models_per_page' => 15,
    'recaptcha_secret'                  => 'test_recaptcha_secret',
    'client_url'                        => 'http://localhost',
    'api_url'                           => 'http://localhost/api',
    'msg_alerts' => [
        'transport'    => 'mail',
        'local_admins' => ['admin@example.com' => 'Admin'],
        'from'         => ['noreply@example.com' => 'GLPi Plugins Test'],
    ],
    'oauth' => [
        'github' => ['clientId' => 'test_id', 'clientSecret' => 'test_secret'],
    ],
];

// ---------------------------------------------------------------------------
// Lightweight Slim-2-compatible mock objects used by Tool and PaginatedCollection
// ---------------------------------------------------------------------------

/**
 * Mimics Slim\Helper\Set – supports array-access reads and offsetSet writes,
 * plus a set() method used by Tool::endWithJson().
 */
class MockHeadersSet implements ArrayAccess
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = array_change_key_case($data, CASE_LOWER);
    }

    public function set(string $key, string $value): void
    {
        $this->data[strtolower($key)] = $value;
    }

    public function get(string $key)
    {
        return $this->data[strtolower($key)] ?? null;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->data[strtolower((string) $offset)]);
    }

    public function offsetGet($offset)
    {
        return $this->data[strtolower((string) $offset)] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->data[strtolower((string) $offset)] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[strtolower((string) $offset)]);
    }

    public function all(): array
    {
        return $this->data;
    }
}

class MockSlimRequest
{
    public MockHeadersSet $headers;
    private string $body;
    private string $method;
    private string $uri;

    public function __construct(array $headers = [], string $body = '', string $method = 'GET', string $uri = '/')
    {
        $this->headers = new MockHeadersSet($headers);
        $this->body    = $body;
        $this->method  = $method;
        $this->uri     = $uri;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getResourceUri(): string
    {
        return $this->uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}

class MockSlimResponse
{
    public MockHeadersSet $headers;
    private int $statusCode = 200;

    public function __construct()
    {
        $this->headers = new MockHeadersSet();
    }

    public function status(int $code): void
    {
        $this->statusCode = $code;
    }

    public function getStatus(): int
    {
        return $this->statusCode;
    }
}

/**
 * Drop-in replacement for the global $app in unit tests.
 * halt() stores arguments and throws Slim\Exception\Stop so makeEndpoint()
 * behaves correctly (it swallows Stop silently).
 */
class MockSlimApp
{
    public MockSlimRequest  $request;
    public MockSlimResponse $response;

    /** Set by halt(); null if halt() was never called. */
    public ?array $halted = null;

    public function __construct(array $requestHeaders = [], string $requestBody = '')
    {
        $this->request  = new MockSlimRequest($requestHeaders, $requestBody);
        $this->response = new MockSlimResponse();
    }

    public function halt(int $code, string $body = '')
    {
        $this->halted = [$code, $body];
        throw new \Slim\Exception\Stop();
    }
}
