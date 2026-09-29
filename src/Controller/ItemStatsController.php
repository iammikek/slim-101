<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ItemService;
use App\Support\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ItemStatsController
{
    public function __construct(private readonly ItemService $itemService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return Http::jsonResponse($this->itemService->getStats());
    }
}
