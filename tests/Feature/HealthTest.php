<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\ApiTestCase;

class HealthTest extends ApiTestCase
{
    public function test_root(): void
    {
        $this->getJson('/')
            ->assertOk()
            ->assertExactJson(['message' => 'Hello from slim-101']);
    }

    public function test_health(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }
}
