<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exception\CategoryInUseException;
use App\Exception\CategoryNameExistsException;
use App\Exception\CategoryNotFoundException;
use App\Exception\ItemNotFoundException;
use App\Exception\UserEmailExistsException;
use App\Support\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ExceptionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ItemNotFoundException) {
            return Http::errorResponse('Item not found', 404, 'ITEM_NOT_FOUND');
        } catch (CategoryNotFoundException) {
            return Http::errorResponse('Category not found', 404, 'CATEGORY_NOT_FOUND');
        } catch (CategoryInUseException) {
            return Http::errorResponse('Category has items and cannot be deleted', 409, 'CATEGORY_IN_USE');
        } catch (CategoryNameExistsException $e) {
            return Http::errorResponse("Category name '{$e->name}' already exists", 409, 'CATEGORY_NAME_EXISTS');
        } catch (UserEmailExistsException $e) {
            return Http::errorResponse("User email '{$e->email}' already exists", 409, 'USER_EMAIL_EXISTS');
        }
    }
}
