<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ItemNotFoundException;
use PDO;

class ItemService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly CategoryService $categoryService,
    ) {
    }

    /** @param array<string, mixed> $filters
     * @return array{0: list<array<string, mixed>>, 1: int}
     */
    public function listItems(int $skip, int $limit, array $filters = []): array
    {
        [$where, $params] = $this->buildFilterClause($filters);

        $countSql = 'SELECT COUNT(*) FROM items i' . $where;
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = 'SELECT i.*, c.id AS cat_id, c.name AS cat_name, c.description AS cat_description
                FROM items i
                LEFT JOIN categories c ON c.id = i.category_id'
            . $where
            . ' ORDER BY i.id LIMIT ? OFFSET ?';

        $stmt = $this->pdo->prepare($sql);
        $bindIndex = 1;
        foreach ($params as $param) {
            $stmt->bindValue($bindIndex++, $param);
        }
        $stmt->bindValue($bindIndex++, $limit, PDO::PARAM_INT);
        $stmt->bindValue($bindIndex, $skip, PDO::PARAM_INT);
        $stmt->execute();

        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[] = $this->hydrateItemRow($row);
        }

        return [$rows, $total];
    }

    /** @return array{item: array<string, mixed>, category: array<string, mixed>|null} */
    public function getById(int $itemId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT i.*, c.id AS cat_id, c.name AS cat_name, c.description AS cat_description
             FROM items i
             LEFT JOIN categories c ON c.id = i.category_id
             WHERE i.id = ?',
        );
        $stmt->execute([$itemId]);
        $row = $stmt->fetch();
        if ($row === false) {
            throw new ItemNotFoundException($itemId);
        }

        return $this->hydrateItemRow($row);
    }

    /** @return array{item: array<string, mixed>, category: array<string, mixed>|null} */
    public function create(string $name, ?string $description, string $price, ?int $categoryId): array
    {
        if ($categoryId !== null) {
            $this->categoryService->getById($categoryId);
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO items (name, description, price, category_id) VALUES (?, ?, ?, ?)',
        );
        $stmt->execute([$name, $description, $price, $categoryId]);

        return $this->getById((int) $this->pdo->lastInsertId());
    }

    /** @param array<string, mixed> $data
     * @return array{item: array<string, mixed>, category: array<string, mixed>|null}
     */
    public function update(int $itemId, array $data): array
    {
        $current = $this->getById($itemId);
        $item = $current['item'];

        if (array_key_exists('name', $data) && $data['name'] !== null) {
            $item['name'] = (string) $data['name'];
        }

        if (array_key_exists('description', $data)) {
            $item['description'] = $data['description'];
        }

        if (array_key_exists('price', $data) && $data['price'] !== null) {
            $item['price'] = (string) $data['price'];
        }

        if (array_key_exists('category_id', $data)) {
            if ($data['category_id'] === null) {
                $item['category_id'] = null;
            } else {
                $category = $this->categoryService->getById((int) $data['category_id']);
                $item['category_id'] = (int) $category['id'];
            }
        }

        $stmt = $this->pdo->prepare(
            'UPDATE items SET name = ?, description = ?, price = ?, category_id = ? WHERE id = ?',
        );
        $stmt->execute([
            $item['name'],
            $item['description'],
            $item['price'],
            $item['category_id'],
            $itemId,
        ]);

        return $this->getById($itemId);
    }

    public function delete(int $itemId): void
    {
        $this->getById($itemId);
        $stmt = $this->pdo->prepare('DELETE FROM items WHERE id = ?');
        $stmt->execute([$itemId]);
    }

    /** @return array<string, mixed> */
    public function getStats(): array
    {
        $total = (int) $this->pdo->query('SELECT COUNT(*) FROM items')->fetchColumn();

        if ($total === 0) {
            return [
                'total_items' => 0,
                'average_price' => 0.0,
                'min_price' => null,
                'max_price' => null,
                'uncategorized_count' => 0,
                'by_category' => [],
            ];
        }

        $aggregate = $this->pdo->query(
            'SELECT AVG(CAST(price AS REAL)) AS avg_price, MIN(CAST(price AS REAL)) AS min_price, MAX(CAST(price AS REAL)) AS max_price FROM items',
        )->fetch();

        $uncategorizedCount = (int) $this->pdo->query(
            'SELECT COUNT(*) FROM items WHERE category_id IS NULL',
        )->fetchColumn();

        $categoryStmt = $this->pdo->query(
            'SELECT categories.id AS category_id, categories.name AS category_name,
                    COUNT(items.id) AS item_count, AVG(CAST(items.price AS REAL)) AS average_price
             FROM items
             INNER JOIN categories ON categories.id = items.category_id
             GROUP BY categories.id, categories.name
             ORDER BY categories.name',
        );

        $byCategory = [];
        foreach ($categoryStmt->fetchAll() as $row) {
            $byCategory[] = [
                'category_id' => (int) $row['category_id'],
                'category_name' => $row['category_name'],
                'item_count' => (int) $row['item_count'],
                'average_price' => round((float) $row['average_price'], 2),
            ];
        }

        return [
            'total_items' => $total,
            'average_price' => round((float) $aggregate['avg_price'], 2),
            'min_price' => round((float) $aggregate['min_price'], 2),
            'max_price' => round((float) $aggregate['max_price'], 2),
            'uncategorized_count' => $uncategorizedCount,
            'by_category' => $byCategory,
        ];
    }

    /** @param array<string, mixed> $filters
     * @return array{0: string, 1: list<mixed>}
     */
    private function buildFilterClause(array $filters): array
    {
        $clauses = [];
        $params = [];

        if (isset($filters['min_price'])) {
            $clauses[] = 'i.price >= ?';
            $params[] = (string) $filters['min_price'];
        }

        if (isset($filters['max_price'])) {
            $clauses[] = 'i.price <= ?';
            $params[] = (string) $filters['max_price'];
        }

        if (isset($filters['category_id'])) {
            $clauses[] = 'i.category_id = ?';
            $params[] = (int) $filters['category_id'];
        }

        if (isset($filters['name_contains'])) {
            $clauses[] = 'LOWER(i.name) LIKE ?';
            $params[] = '%' . strtolower((string) $filters['name_contains']) . '%';
        }

        $where = $clauses === [] ? '' : ' WHERE ' . implode(' AND ', $clauses);

        return [$where, $params];
    }

    /** @param array<string, mixed> $row
     * @return array{item: array<string, mixed>, category: array<string, mixed>|null}
     */
    private function hydrateItemRow(array $row): array
    {
        $item = [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => $row['price'],
            'category_id' => $row['category_id'] !== null ? (int) $row['category_id'] : null,
        ];

        $category = null;
        if ($row['cat_id'] !== null) {
            $category = [
                'id' => (int) $row['cat_id'],
                'name' => $row['cat_name'],
                'description' => $row['cat_description'],
            ];
        }

        return ['item' => $item, 'category' => $category];
    }
}
