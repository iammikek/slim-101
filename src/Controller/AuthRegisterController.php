<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UserService;
use App\Support\ApiSerializer;
use App\Support\Http;
use App\Support\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthRegisterController
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $payload = Http::parseJsonBody((string) $request->getBody());
        if ($payload === null) {
            return Http::errorResponse('Invalid JSON body', 422);
        }

        $error = Validator::firstError($payload, [
            'email' => ['required', 'email', 'min:5', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:128'],
        ]);
        if ($error !== null) {
            return Http::errorResponse($error, 422);
        }

        $user = $this->userService->create((string) $payload['email'], (string) $payload['password']);

        return Http::jsonResponse(ApiSerializer::user($user), 201);
    }
}
