<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UserService;
use App\Support\Http;
use App\Support\JwtService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthLoginController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly JwtService $jwtService,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $contentType = $request->getHeaderLine('Content-Type');
        $email = '';
        $password = '';

        if (str_contains($contentType, 'application/json')) {
            $payload = Http::parseJsonBody((string) $request->getBody());
            if (is_array($payload)) {
                $email = (string) ($payload['username'] ?? $payload['email'] ?? '');
                $password = (string) ($payload['password'] ?? '');
            }
        } else {
            $form = Http::parseFormBody((string) $request->getBody());
            $email = (string) ($form['username'] ?? '');
            $password = (string) ($form['password'] ?? '');
        }

        if ($email === '' || $password === '') {
            $query = $request->getQueryParams();
            $email = (string) ($query['username'] ?? $email);
            $password = (string) ($query['password'] ?? $password);
        }

        $user = $this->userService->authenticate($email, $password);
        if ($user === null) {
            return Http::errorResponse('Incorrect email or password', 401, null, [
                'WWW-Authenticate' => 'Bearer',
            ]);
        }

        return Http::jsonResponse([
            'access_token' => $this->jwtService->createToken($user),
            'token_type' => 'bearer',
        ]);
    }
}
