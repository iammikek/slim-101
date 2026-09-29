<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\UserEmailExistsException;
use PDO;

class UserService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, mixed>|null */
    public function getByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /** @return array<string, mixed> */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /** @return array<string, mixed> */
    public function create(string $email, string $password): array
    {
        if ($this->getByEmail($email) !== null) {
            throw new UserEmailExistsException($email);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
        $stmt->execute([$email, $hash]);

        return [
            'id' => (int) $this->pdo->lastInsertId(),
            'email' => $email,
            'password' => $hash,
        ];
    }

    /** @return array<string, mixed>|null */
    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->getByEmail($email);
        if ($user === null || ! password_verify($password, (string) $user['password'])) {
            return null;
        }

        return $user;
    }
}
