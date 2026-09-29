<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Service\UserService;
use App\Support\Http;
use App\Support\JwtService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class JwtAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly JwtService $jwtService,
        private readonly UserService $userService,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeaderLine('Authorization');
        if (! str_starts_with($header, 'Bearer ')) {
            return Http::errorResponse('Unauthorized', 401);
        }

        $claims = $this->jwtService->decodeToken(substr($header, 7));
        if ($claims === null) {
            return Http::errorResponse('Unauthorized', 401);
        }

        $user = $this->userService->getById($claims['sub']);
        if ($user === null) {
            return Http::errorResponse('Unauthorized', 401);
        }

        return $handler->handle($request->withAttribute('user', $user));
    }
}
