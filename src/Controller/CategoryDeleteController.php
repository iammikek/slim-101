<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CategoryService;
use App\Support\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CategoryDeleteController
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $this->categoryService->delete((int) $request->getAttribute('categoryId'));

        return Http::noContent();
    }
}
