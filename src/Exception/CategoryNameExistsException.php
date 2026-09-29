<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

class CategoryNameExistsException extends RuntimeException
{
    public function __construct(public readonly string $name)
    {
        parent::__construct("Category name '{$name}' already exists");
    }
}
