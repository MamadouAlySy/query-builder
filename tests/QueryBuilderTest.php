<?php

namespace MamadouAlySy\Tests;

use MamadouAlySy\Exceptions\QueryBuilderException;
use MamadouAlySy\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    protected QueryBuilder $queryBuilder;

    /**
     * @throws QueryBuilderException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->queryBuilder = new QueryBuilder(new Connection);
        $this->queryBuilder->drop()->table('user')->commit();
    }
    

    public function testInsertQuery()
    {
        $query = $this->queryBuilder
            ->insert(['name' => 'Mamadou', 'age' => 23])
            ->into('user')
            ->getQuery();

        $this->assertEquals(
            'INSERT INTO `user`(`name`, `age`) VALUES(:name, :age);',
            $query->getSql()
        );
    }

    public function testSimpleSelectQueryWithoutConditions()
    {
        $query = $this->queryBuilder
            ->from('user')
            ->select()
            ->everything()
            ->getQuery();

        $this->assertEquals('SELECT * FROM `user`;', $query->getSql());
    }

    public function testSimpleSelectQueryWithLimitAndOffset()
    {
        $query = $this->queryBuilder
            ->from('user')
            ->limit(10)
            ->offset(5)
            ->select()
            ->getQuery();

        $this->assertEquals('SELECT * FROM `user` LIMIT 10 OFFSET 5;', $query->getSql());
    }

    public function testSelectQueryWithConditions()
    {
        $query = $this->queryBuilder
            ->from('user')
            ->where('id')->greaterThan(10)
            ->orWhere('name')->equal('Mamadou')
            ->select()
            ->getQuery();

        $this->assertEquals(
            'SELECT * FROM `user` WHERE id > :cid OR name = :cname;',
            $query->getSql()
        );

        $this->assertEquals(
            [
                'cid'   => 10,
                'cname' => "Mamadou",
            ],
            $query->getParameters()
        );
    }

    public function testUpdateQuery()
    {
        $query = $this->queryBuilder
            ->from('user')
            ->update(['name' => 'Mamadou'])
            ->where('id')->equal(1)
            ->getQuery();

        $this->assertEquals(
            'UPDATE `user` SET name = :name WHERE id = :cid;',
            $query->getSql()
        );

        $this->assertEquals(
            [
                'name' => "Mamadou",
                'cid'  => 1,
            ],
            $query->getParameters()
        );
    }

    public function testDeleteQuery()
    {
        $query = $this->queryBuilder
            ->from('user')
            ->where('id')->equal(1)
            ->delete()
            ->getQuery();

        $this->assertEquals(
            'DELETE FROM `user` WHERE id = :cid;',
            $query->getSql()
        );

        $this->assertEquals(
            [
                'cid' => 1,
            ],
            $query->getParameters()
        );
    }

    public function testShouldThrowAnExceptionOnCommit()
    {
        $this->queryBuilder->setConnection(null);
        $this->expectException(QueryBuilderException::class);
        $this->queryBuilder->select()->from('users')->commit();
    }

    public function testCreateQuery()
    {
        $query = $this->queryBuilder->create()
            ->table('user')
            ->field('id')->int()->notNull()->autoIncrement()
            ->field('username')->string()->default('mamadou')
            ->getQuery();

        $this->assertEquals(
            'CREATE TABLE `user`(id INT(11) NOT NULL AUTO_INCREMENT, username VARCHAR(255) DEFAULT :username);',
            $query->getSql()
        );

        $this->assertEquals(
            ['username' => 'mamadou'],
            $query->getParameters()
        );
    }

    /**
     * @throws QueryBuilderException
     */
    public function testCanCommitQuery()
    {
        $process = $this->queryBuilder->create()
            ->table('user')
            ->field('id')->int()->notNull()->primaryKey()->autoIncrement()
            ->field('username')->string()->notNull()
            ->field('password')->string()->notNull()
            ->field('status')->int(1)->notNull()->default(0)
            ->commit();

        $this->assertTrue($process);
    }
}
