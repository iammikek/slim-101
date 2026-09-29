<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

final class Database
{
    public static function createPdo(string $path): PDO
    {
        $pdo = new PDO('sqlite:' . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }

    public static function migrate(PDO $pdo): void
    {
        $schemaPath = dirname(__DIR__, 2) . '/database/schema.sql';
        $pdo->exec((string) file_get_contents($schemaPath));
    }

    public static function resetTables(PDO $pdo): void
    {
        $pdo->exec('DELETE FROM items');
        $pdo->exec('DELETE FROM categories');
        $pdo->exec('DELETE FROM users');
    }
}
