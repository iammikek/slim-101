<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\ApiTestCase;

class CategoriesTest extends ApiTestCase
{
    public function test_create_category(): void
    {
        $token = $this->createAuthenticatedToken();

        $response = $this->withHeaders($this->bearerHeaders($token))
            ->postJson('/categories', [
                'name' => 'Tools',
                'description' => 'Hand tools',
            ]);

        $response->assertCreated();
        $this->assertGreaterThanOrEqual(1, $response->json()['id']);
        $response->assertJsonPath('name', 'Tools');
        $response->assertJsonPath('description', 'Hand tools');
    }

    public function test_create_category_without_auth(): void
    {
        $this->postJson('/categories', ['name' => 'Tools'])->assertUnauthorized();
    }

    public function test_create_category_duplicate_name(): void
    {
        $token = $this->createAuthenticatedToken();
        $headers = $this->bearerHeaders($token);

        $this->withHeaders($headers)->postJson('/categories', ['name' => 'foo'])->assertCreated();
        $this->withHeaders($headers)
            ->postJson('/categories', ['name' => 'foo', 'description' => 'duplicate'])
            ->assertStatus(409)
            ->assertJsonPath('code', 'CATEGORY_NAME_EXISTS');
    }

    public function test_list_categories(): void
    {
        $token = $this->createAuthenticatedToken();
        $headers = $this->bearerHeaders($token);

        $this->withHeaders($headers)->postJson('/categories', ['name' => 'Books'])->assertCreated();

        $this->getJson('/categories')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('items.0.name', 'Books');
    }
}
