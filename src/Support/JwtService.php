<?php

declare(strict_types=1);

namespace App\Support;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class JwtService
{
    public function __construct(private readonly string $secret)
    {
    }

    /** @param array<string, mixed> $user */
    public function createToken(array $user): string
    {
        $now = time();

        return JWT::encode([
            'sub' => (int) $user['id'],
            'email' => $user['email'],
            'iat' => $now,
            'exp' => $now + 3600,
        ], $this->secret, 'HS256');
    }

    /** @return array{sub: int, email: string}|null */
    public function decodeToken(string $token): ?array
    {
        try {
            $payload = JWT::decode($token, new Key($this->secret, 'HS256'));

            return [
                'sub' => (int) $payload->sub,
                'email' => (string) $payload->email,
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
