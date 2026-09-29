<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\ApiTestCase;

class ItemsListTest extends ApiTestCase
{
    public function test_list_items_empty(): void
    {
        $this->getJson('/items')
            ->assertOk()
            ->assertExactJson([
                'items' => [],
                'total' => 0,
                'skip' => 0,
                'limit' => 10,
            ]);
    }

    public function test_list_items_with_pagination(): void
    {
        $token = $this->createAuthenticatedToken();
        $headers = $this->bearerHeaders($token);

        foreach ([['A', 1.0], ['B', 2.0], ['C', 3.0]] as [$name, $price]) {
            $this->withHeaders($headers)
                ->postJson('/items', ['name' => $name, 'price' => $price])
                ->assertCreated();
        }

        $response = $this->getJson('/items?skip=1&limit=2');

        $response->assertOk();
        $response->assertJsonPath('total', 3);
        $response->assertJsonPath('skip', 1);
        $response->assertJsonPath('limit', 2);
        $response->assertJsonCount(2, 'items');
        $response->assertJsonPath('items.0.name', 'B');
        $response->assertJsonPath('items.1.name', 'C');
    }

    public function test_list_items_validation_errors(): void
    {
        $this->getJson('/items?limit=101')->assertStatus(422);
    }
}
