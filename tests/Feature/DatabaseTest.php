<?php

use App\Models\Contest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_contest_with_factory()
    {
        $contest = Contest::factory()->create([
            'rating_max' => 10,
        ]);

        $this->assertNotNull($contest);
        $this->assertEquals(10, $contest->rating_max);
    }
}
