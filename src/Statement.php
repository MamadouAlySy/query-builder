<?php

declare(strict_types=1);

namespace MamadouAlySy\QueryBuilder;

abstract class Statement
{
    abstract public function sql() : string;
    abstract public function run() : mixed;
}