<?php

declare(strict_types=1);

namespace MamadouAlySy\QueryBuilder;

use MamadouAlySy\QueryBuilder\Manipulation\DeleteQuery;

class QueryBuilder
{
    public function delete(): DeleteQuery
    {
        return new DeleteQuery();
    }
}
