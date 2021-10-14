<?php

declare(strict_types=1);

namespace MamadouAlySy\Interfaces;

interface ConnectionInterface
{
    public function open(): \PDO;
}
