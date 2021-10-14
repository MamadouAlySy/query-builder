<?php

namespace MamadouAlySy\Tests;

use MamadouAlySy\Exceptions\QueryBuilderException;
use MamadouAlySy\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    protected QueryBuilder $queryBuilder;

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
            $query->getSql(),
            'INSERT INTO user(`name`, `age`) VALUES(:name, :age);'
        );
    }

    public function testSimpleSelectQueryWithoutConditions()
    {
        $query = $this->queryBuilder
            ->from('user')
            ->select()
            ->getQuery();

        $this->assertEquals($query->getSql(), 'SELECT * FROM user;');
    }

    public function testSimpleSelectQueryWithLimitAndOffset()
    {
        $query = $this->queryBuilder
            ->from('user')
            ->limit(10)
            ->offset(5)
            ->select()
            ->getQuery();

        $this->assertEquals($query->getSql(), 'SELECT * FROM user LIMIT 10 OFFSET 5;');
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
            $query->getSql(),
            'SELECT * FROM user WHERE id > :cid OR name = :cname;'
        );

        $this->assertEquals(
            $query->getParameters(),
            [
                'cid'   => 10,
                'cname' => "Mamadou",
            ]
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
            $query->getSql(),
            'UPDATE user SET name = :name WHERE id = :cid;'
        );

        $this->assertEquals(
            $query->getParameters(),
            [
                'name' => "Mamadou",
                'cid'  => 1,
            ]
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
            $query->getSql(),
            'DELETE FROM user WHERE id = :cid;'
        );

        $this->assertEquals(
            $query->getParameters(),
            [
                'cid' => 1,
            ]
        );
    }

    public function testShoudThrowAnExceptionOnCommit()
    {
        $this->queryBuilder->setConncetion(null);
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
            $query->getSql(),
            'CREATE TABLE `user`(id INT(11) NOT NULL AUTO_INCREMENT, username VARCHAR(255) DEFAULT :username);'
        );

        $this->assertEquals(
            $query->getParameters(),
            ['username' => 'mamadou',]
        );
    }
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
