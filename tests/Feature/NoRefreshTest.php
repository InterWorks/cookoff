<?php

use Tests\TestCase;

class NoRefreshTest extends TestCase
{
    public function test_simple_check_without_database()
    {
        $this->assertTrue(true);
        $this->assertEquals(2, 1 + 1);
    }
}
