<?php

declare(strict_types=1);

namespace App\Controller;

use App\Support\ApiSerializer;
use App\Support\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthMeController
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user');
        if (! is_array($user)) {
            return Http::errorResponse('Unauthorized', 401);
        }

        return Http::jsonResponse(ApiSerializer::user($user));
    }
}
