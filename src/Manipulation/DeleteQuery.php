<?php

declare(strict_types=1);

namespace MamadouAlySy\QueryBuilder\Manipulation;

use MamadouAlySy\QueryBuilder\Manipulation\Traits\FromTrait;
use MamadouAlySy\QueryBuilder\Manipulation\Traits\WhereTrait;
use MamadouAlySy\QueryBuilder\Query;

class DeleteQuery extends Query
{
    use FromTrait;
    use WhereTrait;

    /**
     * @inheritDoc
     */
    public function getSql(): string
    {
        return 'DELETE'
            . $this->getFromStatement()
            . $this->getWhereStatement()
            . ';';
    }

    /**
     * @inheritDoc
     */
    public function getParameters(): array
    {
        return $this->getConditionValues();
    }
}
