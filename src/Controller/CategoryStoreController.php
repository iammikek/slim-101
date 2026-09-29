<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CategoryService;
use App\Support\ApiSerializer;
use App\Support\Http;
use App\Support\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CategoryStoreController
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

        $error = Validator::firstError($payload, [
            'name' => ['required', 'string', 'min:1', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);
        if ($error !== null) {
            return Http::errorResponse($error, 422);
        }

        $category = $this->categoryService->create(
            (string) $payload['name'],
            $payload['description'] ?? null,
        );

        return Http::jsonResponse(ApiSerializer::category($category), 201);
    }
}
