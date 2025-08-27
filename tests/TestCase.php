<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock console interactions to prevent prompts during testing
        if (app()->runningInConsole()) {
            $this->artisan('config:clear');
        }
    }
}
