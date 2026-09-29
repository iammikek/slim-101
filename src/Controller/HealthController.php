<?php

declare(strict_types=1);

namespace App\Controller;

use App\Support\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HealthController
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return Http::jsonResponse(['status' => 'ok']);
    }
}
