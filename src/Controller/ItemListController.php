<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ItemService;
use App\Support\ApiSerializer;
use App\Support\Http;
use App\Support\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ItemListController
{
    public function __construct(private readonly ItemService $itemService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getQueryParams();
        $skip = (int) ($query['skip'] ?? 0);
        $limit = (int) ($query['limit'] ?? 10);

        $validationData = [
            'skip' => $skip,
            'limit' => $limit,
            'min_price' => $query['min_price'] ?? null,
            'max_price' => $query['max_price'] ?? null,
            'category_id' => $query['category_id'] ?? null,
            'name_contains' => $query['name_contains'] ?? null,
        ];

        $error = Validator::firstError($validationData, [
            'skip' => ['integer', 'min:0'],
            'limit' => ['integer', 'min:1', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'gt:0'],
            'max_price' => ['nullable', 'numeric', 'gt:0'],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'name_contains' => ['nullable', 'string', 'min:1', 'max:255'],
        ]);
        if ($error !== null) {
            return Http::errorResponse($error, 422);
        }

        $filters = [];
        if (array_key_exists('min_price', $query)) {
            $filters['min_price'] = $query['min_price'];
        }
        if (array_key_exists('max_price', $query)) {
            $filters['max_price'] = $query['max_price'];
        }
        if (array_key_exists('category_id', $query)) {
            $filters['category_id'] = (int) $query['category_id'];
        }
        if (array_key_exists('name_contains', $query)) {
            $filters['name_contains'] = $query['name_contains'];
        }

        [$rows, $total] = $this->itemService->listItems($skip, $limit, $filters);

        $items = array_map(
            static fn (array $row) => ApiSerializer::item($row['item'], $row['category']),
            $rows,
        );

        return Http::jsonResponse([
            'items' => $items,
            'total' => $total,
            'skip' => $skip,
            'limit' => $limit,
        ]);
    }
}
