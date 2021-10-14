<?php

declare(strict_types=1);

namespace MamadouAlySy\Tests;

use MamadouAlySy\Interfaces\ConnectionInterface;
use PDO;

class Connection implements ConnectionInterface
{
    public function open(): PDO
    {
        $credentials = [
            'dsn'      => 'sqlite:sqlite3.db',
            'user'     => '',
            'password' => '',
        ];

        return new PDO(
            $credentials['dsn'],
            $credentials['user'] ?? 'root',
            $credentials['password'] ?? '',
            $credentials['options'] ?? null
        );
    }
}
