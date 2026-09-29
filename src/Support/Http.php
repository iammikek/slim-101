<?php

declare(strict_types=1);

namespace App\Support;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

final class Http
{
    /** @param array<string, mixed> $data */
    public static function jsonResponse(array $data, int $status = 200, array $headers = []): ResponseInterface
    {
        $body = json_encode($data, JSON_THROW_ON_ERROR);
        $response = new Response($status);
        $response->getBody()->write($body);

        $response = $response->withHeader('Content-Type', 'application/json');
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    public static function errorResponse(string $detail, int $status, ?string $code = null, array $headers = []): ResponseInterface
    {
        $payload = ['detail' => $detail];
        if ($code !== null) {
            $payload['code'] = $code;
        }

        return self::jsonResponse($payload, $status, $headers);
    }

    public static function noContent(): ResponseInterface
    {
        return new Response(204);
    }

    /** @return array<string, mixed>|null */
    public static function parseJsonBody(string $body): ?array
    {
        if ($body === '') {
            return null;
        }

        $decoded = json_decode($body, true);
        if (! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    /** @return array<string, string> */
    public static function parseFormBody(string $body): array
    {
        if ($body === '') {
            return [];
        }

        parse_str($body, $parsed);

        return is_array($parsed) ? array_map(static fn ($v) => is_string($v) ? $v : (string) $v, $parsed) : [];
    }
}
