<?php

declare(strict_types=1);

namespace MamadouAlySy\QueryBuilder;

abstract class Query
{
    /**
     * Returns the query sql
     *
     * @return string
     */
    abstract public function getSql(): string;

    /**
     * Returns the query parameters
     *
     * @return array
     */
    abstract public function getParameters(): array;
}
