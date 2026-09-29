<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\ApiTestCase;

class AuthTest extends ApiTestCase
{
    public function test_register_user(): void
    {
        $response = $this->postJson('/auth/register', [
            'email' => 'alice@example.com',
            'password' => 'password123',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('email', 'alice@example.com');
        $this->assertArrayNotHasKey('password', $response->json());
    }

    public function test_register_duplicate_email(): void
    {
        $payload = ['email' => 'test@example.com', 'password' => 'secret123'];

        $this->postJson('/auth/register', $payload)->assertCreated();
        $this->postJson('/auth/register', $payload)
            ->assertStatus(409)
            ->assertJsonPath('code', 'USER_EMAIL_EXISTS');
    }

    public function test_login_success(): void
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
        $response->assertJsonPath('token_type', 'bearer');
        $this->assertNotEmpty($response->json()['access_token']);
    }

    public function test_login_invalid_password(): void
    {
        $this->postJson('/auth/register', [
            'email' => 'test@example.com',
            'password' => 'secret123',
        ])->assertCreated();

        $this->post('/auth/login', [
            'username' => 'test@example.com',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('detail', 'Incorrect email or password');
    }

    public function test_read_current_user(): void
    {
        $token = $this->createAuthenticatedToken();

        $this->withHeaders($this->bearerHeaders($token))
            ->getJson('/auth/me')
            ->assertOk()
            ->assertJsonPath('email', 'test@example.com');
    }

    public function test_read_current_user_without_token(): void
    {
        $this->getJson('/auth/me')->assertUnauthorized();
    }
}
