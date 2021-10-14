<?php

declare(strict_types=1);

namespace MamadouAlySy\Interfaces;

use PDO;

interface ConnectionInterface
{
    public function open(): PDO;
}
