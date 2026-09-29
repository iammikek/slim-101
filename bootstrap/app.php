<?php

declare(strict_types=1);

use App\Database\Database;
use App\Middleware\ExceptionMiddleware;
use App\Support\JwtService;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;

require dirname(__DIR__) . '/vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$databasePath = $_ENV['DATABASE_PATH'] ?? getenv('DATABASE_PATH') ?: 'database/database.sqlite';
$jwtSecret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: 'change-me-in-production';

$pdo = Database::createPdo($databasePath);
if ($databasePath === ':memory:') {
    Database::migrate($pdo);
}

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    PDO::class => $pdo,
    JwtService::class => static fn () => new JwtService($jwtSecret),
]);
$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->add(ExceptionMiddleware::class);
$app->addRoutingMiddleware();
$app->addErrorMiddleware(false, true, true);

require __DIR__ . '/routes.php';

return $app;
