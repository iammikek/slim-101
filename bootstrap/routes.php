<?php

declare(strict_types=1);

use App\Controller\AuthLoginController;
use App\Controller\AuthMeController;
use App\Controller\AuthRegisterController;
use App\Controller\CategoryDeleteController;
use App\Controller\CategoryListController;
use App\Controller\CategoryShowController;
use App\Controller\CategoryStoreController;
use App\Controller\CategoryUpdateController;
use App\Controller\HealthController;
use App\Controller\HealthRootController;
use App\Controller\ItemDeleteController;
use App\Controller\ItemListController;
use App\Controller\ItemShowController;
use App\Controller\ItemStatsController;
use App\Controller\ItemStoreController;
use App\Controller\ItemUpdateController;
use App\Middleware\JwtAuthMiddleware;
use Slim\App;

/** @var App $app */

$app->get('/', HealthRootController::class);
$app->get('/health', HealthController::class);

$app->post('/auth/register', AuthRegisterController::class);
$app->post('/auth/login', AuthLoginController::class);
$app->get('/auth/me', AuthMeController::class)->add(JwtAuthMiddleware::class);

$app->get('/items/stats/summary', ItemStatsController::class);
$app->get('/items', ItemListController::class);
$app->get('/items/{itemId}', ItemShowController::class);

$app->post('/items', ItemStoreController::class)->add(JwtAuthMiddleware::class);
$app->patch('/items/{itemId}', ItemUpdateController::class)->add(JwtAuthMiddleware::class);
$app->delete('/items/{itemId}', ItemDeleteController::class)->add(JwtAuthMiddleware::class);

$app->get('/categories', CategoryListController::class);
$app->get('/categories/{categoryId}', CategoryShowController::class);
$app->post('/categories', CategoryStoreController::class)->add(JwtAuthMiddleware::class);
$app->patch('/categories/{categoryId}', CategoryUpdateController::class)->add(JwtAuthMiddleware::class);
$app->delete('/categories/{categoryId}', CategoryDeleteController::class)->add(JwtAuthMiddleware::class);
