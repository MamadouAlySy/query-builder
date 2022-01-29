<?php

use MamadouAlySy\QueryBuilder\Manupulation\Delete;
use PHPUnit\Framework\TestCase;

class DeleteTest extends TestCase
{
    protected Delete $delete;

    protected function setUp(): void
    {
        parent::setUp();
        $this->delete = new Delete();
    }
    
    /**
     * @test
     */
    public function can_delete_from_a_table(): void
    {
        $this->assertEquals(
            "DELETE FROM `users`;",
            $this->delete->from('users')->sql()
        );
    }

    /**
     * @test
     */
    public function can_delete_from_a_table_with_where_clause(): void
    {
        $this->assertEquals(
            "DELETE FROM `users` WHERE id = 5;",
            $this->delete->from('users')->where('id', '=', 5)->sql()
        );
    }

    /**
     * @test
     */
    public function can_delete_from_a_table_with_where_clause_and_limit(): void
    {
        $this->assertEquals(
            "DELETE FROM `users` LIMIT 1;",
            $this->delete->from('users')->limit(1)->sql()
        );
    }
}