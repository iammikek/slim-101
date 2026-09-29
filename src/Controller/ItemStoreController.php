<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ItemService;
use App\Support\ApiSerializer;
use App\Support\Http;
use App\Support\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ItemStoreController
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

        $error = Validator::firstError($payload, [
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'gt:0'],
            'category_id' => ['nullable', 'integer', 'min:1'],
        ]);
        if ($error !== null) {
            return Http::errorResponse($error, 422);
        }

        $row = $this->itemService->create(
            (string) $payload['name'],
            $payload['description'] ?? null,
            number_format((float) $payload['price'], 2, '.', ''),
            isset($payload['category_id']) ? (int) $payload['category_id'] : null,
        );

        return Http::jsonResponse(ApiSerializer::item($row['item'], $row['category']), 201);
    }
}
