<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

class ItemNotFoundException extends RuntimeException
{
    public function __construct(public readonly int $itemId)
    {
        parent::__construct("Item {$itemId} not found");
    }
}
