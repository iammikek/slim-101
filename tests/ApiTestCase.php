<?php

declare(strict_types=1);

namespace Tests;

use App\Database\Database;
use PDO;

abstract class ApiTestCase extends TestCase
{
    protected PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        $path = dirname(__DIR__) . '/database/test.sqlite';
        $pdo = Database::createPdo($path);
        Database::migrate($pdo);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $path = dirname(__DIR__) . '/database/test.sqlite';
        $this->pdo = Database::createPdo($path);
        Database::resetTables($this->pdo);
    }

    /** @return array<string, string> */
    protected function bearerHeaders(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }

    protected function createAuthenticatedToken(): string
    {
        $this->postJson('/auth/register', [
            'email' => 'test@example.com',
            'password' => 'secret123',
        ])->assertCreated();

        $response = $this->post('/auth/login', [
            'username' => 'test@example.com',
            'password' => 'secret123',
        ]);

        $response->assertOk();

        return $response->json()['access_token'];
    }
}
