<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Tests\Support\TestResponse;

abstract class TestCase extends BaseTestCase
{
    protected App $app;

    /** @var array<string, string> */
    protected array $defaultHeaders = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->defaultHeaders = [];
        $this->app = require dirname(__DIR__) . '/bootstrap/app.php';
    }

    /** @param array<string, string> $headers */
    protected function request(string $method, string $uri, array $data = [], array $headers = []): TestResponse
    {
        $body = '';
        $requestHeaders = array_merge($this->defaultHeaders, $headers);

        if ($data !== []) {
            if (($requestHeaders['Content-Type'] ?? '') === 'application/json') {
                $body = json_encode($data, JSON_THROW_ON_ERROR);
            } else {
                $body = http_build_query($data);
                $requestHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
            }
        }

        $request = (new ServerRequestFactory())->createServerRequest($method, 'http://localhost' . $uri);
        foreach ($requestHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body !== '') {
            $request = $request->withBody((new StreamFactory())->createStream($body));
        }

        /** @var ResponseInterface $response */
        $response = $this->app->handle($request);

        return new TestResponse($response, $this);
    }

    /** @param array<string, string> $headers */
    protected function get(string $uri, array $headers = []): TestResponse
    {
        return $this->request('GET', $uri, [], $headers);
    }

    /** @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    protected function post(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->request('POST', $uri, $data, $headers);
    }

    /** @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    protected function postJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $headers['Content-Type'] = 'application/json';

        return $this->request('POST', $uri, $data, $headers);
    }

    /** @param array<string, string> $headers */
    protected function getJson(string $uri, array $headers = []): TestResponse
    {
        $headers['Accept'] = 'application/json';

        return $this->get($uri, $headers);
    }

    /** @param array<string, string> $headers */
    protected function deleteJson(string $uri, array $headers = []): TestResponse
    {
        return $this->request('DELETE', $uri, [], $headers);
    }

    /** @param array<string, string> $headers */
    protected function withHeaders(array $headers): static
    {
        $this->defaultHeaders = $headers;

        return $this;
    }
}
