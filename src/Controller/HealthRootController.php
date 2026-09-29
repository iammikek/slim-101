<?php

declare(strict_types=1);

namespace App\Controller;

use App\Support\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HealthRootController
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return Http::jsonResponse(['message' => 'Hello from slim-101']);
    }
}
