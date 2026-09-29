<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Database\Database;

$path = $argv[1] ?? (__DIR__ . '/database.sqlite');
$pdo = Database::createPdo($path);
$pdo->exec((string) file_get_contents(__DIR__ . '/schema.sql'));
echo "Migrated {$path}\n";
