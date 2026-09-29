<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CategoryService;
use App\Support\ApiSerializer;
use App\Support\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CategoryShowController
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $category = $this->categoryService->getById((int) $request->getAttribute('categoryId'));

        return Http::jsonResponse(ApiSerializer::category($category));
    }
}
