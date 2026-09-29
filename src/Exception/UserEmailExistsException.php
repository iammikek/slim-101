<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

class UserEmailExistsException extends RuntimeException
{
    public function __construct(public readonly string $email)
    {
        parent::__construct("User email '{$email}' already exists");
    }
}
