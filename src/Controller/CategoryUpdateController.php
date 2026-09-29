<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CategoryService;
use App\Support\ApiSerializer;
use App\Support\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CategoryUpdateController
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $payload = Http::parseJsonBody((string) $request->getBody());
        if ($payload === null) {
            return Http::errorResponse('Invalid JSON body', 422);
        }

        $category = $this->categoryService->update(
            (int) $request->getAttribute('categoryId'),
            $payload,
        );

        return Http::jsonResponse(ApiSerializer::category($category));
    }
}
