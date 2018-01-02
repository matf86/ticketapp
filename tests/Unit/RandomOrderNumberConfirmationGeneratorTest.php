<?php

namespace Tests\Unit;

use App\RandomConfirmationNumberGenerator;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RandomOrderNumberConfirmationGeneratorTest extends TestCase
{
    /** @test */

    function must_be_24_characters_long()
    {
        $number = (new RandomConfirmationNumberGenerator())->generate();

        $this->assertEquals(24, strlen($number));
    }

    /** @test */

    function can_only_contain_uppercase_letters_and_numbers()
    {
        $number = (new RandomConfirmationNumberGenerator())->generate();

        $this->assertRegExp('/^[A-Z0-9]+$/', $number);
    }

    /** @test */

    function cannot_contain_unambiguous_characters()
    {
        $number = (new RandomConfirmationNumberGenerator())->generate();

        $this->assertFalse(strpos($number,'1'));
        $this->assertFalse(strpos($number,'I'));
        $this->assertFalse(strpos($number,'O'));
        $this->assertFalse(strpos($number,'0'));
    }

    /** @test */

    function must_be_unique()
    {
        $generator = new RandomConfirmationNumberGenerator();

        $confirmationNumbers = array_map(function($i) use ($generator) {
            return $generator->generate();
        },range(1, 100));

        $this->assertCount(100, array_unique($confirmationNumbers));
    }
}
