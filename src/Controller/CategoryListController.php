<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CategoryService;
use App\Support\ApiSerializer;
use App\Support\Http;
use App\Support\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CategoryListController
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getQueryParams();
        $skip = max(0, (int) ($query['skip'] ?? 0));
        $limit = min(100, max(1, (int) ($query['limit'] ?? 10)));

        [$rows, $total] = $this->categoryService->listCategories($skip, $limit);

        return Http::jsonResponse([
            'items' => array_map(static fn (array $row) => ApiSerializer::category($row), $rows),
            'total' => $total,
            'skip' => $skip,
            'limit' => $limit,
        ]);
    }
}
