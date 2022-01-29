<?php

declare(strict_types=1);

namespace MamadouAlySy\QueryBuilder\Manupulation;

use MamadouAlySy\QueryBuilder\Manupulation\Traits\FromTrait;
use MamadouAlySy\QueryBuilder\Manupulation\Traits\LimitTrait;
use MamadouAlySy\QueryBuilder\Manupulation\Traits\WhereTrait;
use MamadouAlySy\QueryBuilder\Statement;

class Delete extends Statement
{
    use FromTrait;
    use WhereTrait;
    use LimitTrait;

    public function sql() : string
    {
        return 'DELETE'
        . $this->renderFrom()
        . $this->renderWhere()
        . $this->renderLimit()
        . ';';
    }

    public function run() : mixed
    {

    }
}