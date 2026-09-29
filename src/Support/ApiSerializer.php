<?php

declare(strict_types=1);

namespace App\Support;

final class ApiSerializer
{
    /** @param array<string, mixed> $category */
    public static function category(array $category): array
    {
        return [
            'id' => (int) $category['id'],
            'name' => $category['name'],
            'description' => $category['description'],
        ];
    }

    /** @param array<string, mixed> $item
     * @param array<string, mixed>|null $category
     */
    public static function item(array $item, ?array $category = null, bool $includeCategory = true): array
    {
        $data = [
            'id' => (int) $item['id'],
            'name' => $item['name'],
            'description' => $item['description'],
            'price' => (float) $item['price'],
            'category_id' => $item['category_id'] !== null ? (int) $item['category_id'] : null,
        ];

        if ($includeCategory && $category !== null) {
            $data['category'] = self::category($category);
        } else {
            $data['category'] = null;
        }

        return $data;
    }

    /** @param array<string, mixed> $user */
    public static function user(array $user): array
    {
        return [
            'id' => (int) $user['id'],
            'email' => $user['email'],
        ];
    }
}
