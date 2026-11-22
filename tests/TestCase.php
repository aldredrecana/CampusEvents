<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tests do not need the built CSS/JS (so they also work on GitHub Actions without npm run build)
        $this->withoutVite();
    }
}
