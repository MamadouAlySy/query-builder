<?php

declare(strict_types=1);

namespace MamadouAlySy\QueryBuilder;

use MamadouAlySy\QueryBuilder\Manupulation\Delete;

class QueryBuilder
{
    public function delete()
    {
        return new Delete();
    }
}