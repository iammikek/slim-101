<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

class CategoryInUseException extends RuntimeException
{
    public function __construct(public readonly int $categoryId)
    {
        parent::__construct("Category {$categoryId} is in use");
    }
}
