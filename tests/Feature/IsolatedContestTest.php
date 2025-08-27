<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IsolatedContestTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_insert_contest_directly_into_database()
    {
        $id = DB::table('contests')->insertGetId([
            'name' => 'Direct DB Test',
            'description' => 'Testing direct DB insertion',
            'voting_type' => 'rating',
            'rating_max' => 10,
            'entry_description_display_type' => 'inline',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $contest = DB::table('contests')->where('id', $id)->first();

        $this->assertNotNull($contest);
        $this->assertEquals('Direct DB Test', $contest->name);
        $this->assertEquals('rating', $contest->voting_type);
    }
}
