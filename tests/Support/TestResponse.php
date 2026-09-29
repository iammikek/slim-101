<?php

declare(strict_types=1);

namespace Tests\Support;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

final class TestResponse
{
    public function __construct(
        public readonly ResponseInterface $response,
        private readonly TestCase $testCase,
    ) {
    }

    public function status(): int
    {
        return $this->response->getStatusCode();
    }

    public function assertOk(): self
    {
        Assert::assertSame(200, $this->status());

        return $this;
    }

    public function assertCreated(): self
    {
        Assert::assertSame(201, $this->status());

        return $this;
    }

    public function assertUnauthorized(): self
    {
        Assert::assertSame(401, $this->status());

        return $this;
    }

    public function assertNotFound(): self
    {
        Assert::assertSame(404, $this->status());

        return $this;
    }

    public function assertNoContent(): self
    {
        Assert::assertSame(204, $this->status());

        return $this;
    }

    public function assertStatus(int $status): self
    {
        Assert::assertSame($status, $this->status());

        return $this;
    }

    public function assertRedirect(?string $location = null): self
    {
        Assert::assertContains($this->status(), [301, 302, 303, 307, 308]);
        if ($location !== null) {
            Assert::assertSame($location, $this->response->getHeaderLine('Location'));
        }

        return $this;
    }

    /** @param array<string, mixed> $data */
    public function assertExactJson(array $data): self
    {
        Assert::assertSame($data, $this->json());

        return $this;
    }

    public function assertJsonPath(string $path, mixed $expected): self
    {
        $value = $this->jsonPath($path);
        Assert::assertSame($expected, $value);

        return $this;
    }

    public function assertJsonCount(int $count, ?string $key = null): self
    {
        $data = $this->json();
        if ($key !== null) {
            Assert::assertIsArray($data[$key] ?? null);
            Assert::assertCount($count, $data[$key]);
        } else {
            Assert::assertCount($count, $data);
        }

        return $this;
    }

    public function assertSee(string $needle): self
    {
        Assert::assertStringContainsString($needle, (string) $this->response->getBody());

        return $this;
    }

    /** @return array<string, mixed> */
    public function json(): array
    {
        $decoded = json_decode((string) $this->response->getBody(), true);
        Assert::assertIsArray($decoded);

        return $decoded;
    }

    public function jsonPath(string $path): mixed
    {
        $data = $this->json();
        foreach (explode('.', $path) as $segment) {
            if (! is_array($data) || ! array_key_exists($segment, $data)) {
                Assert::fail("JSON path {$path} not found");
            }
            $data = $data[$segment];
        }

        return $data;
    }
}
