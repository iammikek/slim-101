<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\ApiTestCase;

class ItemsCreateTest extends ApiTestCase
{
    public function test_create_item(): void
    {
        $token = $this->createAuthenticatedToken();

        $response = $this->withHeaders($this->bearerHeaders($token))
            ->postJson('/items', [
                'name' => 'Widget',
                'description' => 'A nice widget',
                'price' => 9.99,
            ]);

        $response->assertCreated();
        $response->assertJsonPath('name', 'Widget');
        $response->assertJsonPath('description', 'A nice widget');
        $response->assertJsonPath('price', 9.99);
        $this->assertGreaterThanOrEqual(1, $response->json()['id']);
    }

    public function test_create_item_without_auth(): void
    {
        $this->postJson('/items', [
            'name' => 'Widget',
            'price' => 9.99,
        ])->assertUnauthorized();
    }

    public function test_get_item_not_found(): void
    {
        $this->getJson('/items/99')
            ->assertNotFound()
            ->assertJsonPath('code', 'ITEM_NOT_FOUND');
    }

    public function test_delete_item(): void
    {
        $token = $this->createAuthenticatedToken();
        $headers = $this->bearerHeaders($token);

        $itemId = $this->withHeaders($headers)
            ->postJson('/items', ['name' => 'To Delete', 'price' => 1.0])
            ->json()['id'];

        $this->withHeaders($headers)
            ->deleteJson('/items/' . $itemId)
            ->assertNoContent();

        $this->getJson('/items/' . $itemId)->assertNotFound();
    }
}
