<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ItemService;
use App\Support\ApiSerializer;
use App\Support\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ItemUpdateController
{
    public function __construct(private readonly ItemService $itemService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $payload = Http::parseJsonBody((string) $request->getBody());
        if ($payload === null) {
            return Http::errorResponse('Invalid JSON body', 422);
        }

        if (array_key_exists('price', $payload) && $payload['price'] !== null) {
            $payload['price'] = number_format((float) $payload['price'], 2, '.', '');
        }

        $itemId = (int) $request->getAttribute('itemId');
        $row = $this->itemService->update($itemId, $payload);

        return Http::jsonResponse(ApiSerializer::item($row['item'], $row['category']));
    }
}
