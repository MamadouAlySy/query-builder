<?php

namespace MamadouAlySy\Tests;

use MamadouAlySy\Exceptions\QueryBuilderException;
use MamadouAlySy\QueryBuilder\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    private QueryBuilder $queryBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queryBuilder = new QueryBuilder();
    }
    

    public function testDeleteQuery(): void
    {
        $this->assertEquals(
            'DELETE FROM `users`;',
            $this->queryBuilder->delete()->from('users')->getSql()
        );

        $query1 = $this->queryBuilder
            ->delete()->from('users')
            ->where('id', '=', 5)
        ;

        $this->assertEquals(
            'DELETE FROM `users` WHERE id = ?;',
            $query1->getSql()
        );

        $this->assertEquals([5], $query1->getParameters());

        $query2 = $this->queryBuilder
            ->delete()->from('users')
            ->where('id', '=', 5)
            ->orWhere('id', '=', 6)
        ;

        $this->assertEquals(
            'DELETE FROM `users` WHERE id = ? OR id = ?;',
            $query2->getSql()
        );

        $this->assertEquals([5, 6], $query2->getParameters());
    }
}
