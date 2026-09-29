<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\CategoryInUseException;
use App\Exception\CategoryNameExistsException;
use App\Exception\CategoryNotFoundException;
use PDO;

class CategoryService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array{0: list<array<string, mixed>>, 1: int} */
    public function listCategories(int $skip, int $limit): array
    {
        $countStmt = $this->pdo->query('SELECT COUNT(*) FROM categories');
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->pdo->prepare('SELECT * FROM categories ORDER BY id LIMIT ? OFFSET ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $skip, PDO::PARAM_INT);
        $stmt->execute();

        return [$stmt->fetchAll(), $total];
    }

    /** @return list<array<string, mixed>> */
    public function listAllOrderedByName(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM categories ORDER BY name');

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed> */
    public function getById(int $categoryId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$categoryId]);
        $category = $stmt->fetch();
        if ($category === false) {
            throw new CategoryNotFoundException($categoryId);
        }

        return $category;
    }

    /** @return array<string, mixed> */
    public function create(string $name, ?string $description): array
    {
        $this->ensureUniqueName($name);

        $stmt = $this->pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
        $stmt->execute([$name, $description]);

        return $this->getById((int) $this->pdo->lastInsertId());
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(int $categoryId, array $data): array
    {
        $category = $this->getById($categoryId);

        if (array_key_exists('name', $data) && $data['name'] !== null) {
            $this->ensureUniqueName((string) $data['name'], $categoryId);
            $category['name'] = (string) $data['name'];
        }

        if (array_key_exists('description', $data)) {
            $category['description'] = $data['description'];
        }

        $stmt = $this->pdo->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?');
        $stmt->execute([$category['name'], $category['description'], $categoryId]);

        return $this->getById($categoryId);
    }

    public function delete(int $categoryId): void
    {
        $category = $this->getById($categoryId);

        $stmt = $this->pdo->prepare('SELECT 1 FROM items WHERE category_id = ? LIMIT 1');
        $stmt->execute([$category['id']]);
        if ($stmt->fetch() !== false) {
            throw new CategoryInUseException($categoryId);
        }

        $delete = $this->pdo->prepare('DELETE FROM categories WHERE id = ?');
        $delete->execute([$categoryId]);
    }

    private function ensureUniqueName(string $name, ?int $excludeId = null): void
    {
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare('SELECT 1 FROM categories WHERE name = ? AND id != ? LIMIT 1');
            $stmt->execute([$name, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT 1 FROM categories WHERE name = ? LIMIT 1');
            $stmt->execute([$name]);
        }

        if ($stmt->fetch() !== false) {
            throw new CategoryNameExistsException($name);
        }
    }
}
