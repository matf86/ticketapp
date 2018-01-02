<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp()
    {
        parent::setUp();

        \Mockery::getConfiguration()->allowMockingNonExistentMethods(false);

        TestResponse::macro('data', function($key) {
            return $this->getOriginalContent()->getData()[$key];
        });
    }
}
