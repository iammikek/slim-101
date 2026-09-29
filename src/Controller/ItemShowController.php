<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ItemService;
use App\Support\ApiSerializer;
use App\Support\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ItemShowController
{
    public function __construct(private readonly ItemService $itemService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $itemId = (int) $request->getAttribute('itemId');
        $row = $this->itemService->getById($itemId);

        return Http::jsonResponse(ApiSerializer::item($row['item'], $row['category']));
    }
}
