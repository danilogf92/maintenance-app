<?php

namespace Tests;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Never warm or invalidate the application's persistent page cache from a test.
        config(['page-cache.store' => 'array']);
    }
}
