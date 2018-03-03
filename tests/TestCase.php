<?php

namespace Tests;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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


        EloquentCollection::macro('assertContains', function($value) {
            \PHPUnit\Framework\Assert::assertTrue($this->contains($value), 'Failed to assert that collection contains specified value.');
        });

        EloquentCollection::macro('assertNotContains', function($value) {
            \PHPUnit\Framework\Assert::assertFalse($this->contains($value), 'Failed to assert that collection do not contain specified value.');
        });

        EloquentCollection::macro('assertEquals', function($items) {

            \PHPUnit\Framework\Assert::assertEquals(count($this), count($items));

            $this->zip($items)->each(function($pair) {
                list($a, $b) = $pair;

                \PHPUnit\Framework\Assert::assertTrue($a->is($b));
            });
        }, 'Failed to assert that collection do not contain specified value.');
    }
}
