<?php

namespace Tests\Unit;

use App\HashidsTicketCodeGenerator;
use App\Ticket;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HashidsTicketCodeGeneratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */

    function code_is_at_least_6_characters_long()
    {
        $generator = new HashidsTicketCodeGenerator('testsalt1');

        $code = $generator->generateFor(new Ticket(['id' => 1]));

        $this->assertGreaterThanOrEqual(6, strlen($code));
    }

    /** @test */

    function code_can_only_contain_uppercase_letters()
    {
        $code = (new HashidsTicketCodeGenerator('testsalt1'))->generateFor(new Ticket(['id' => 1]));

        $this->assertRegExp('/^[A-Z]+$/', $code);
    }
    
    /** @test */
    
    function codes_for_the_same_ticket_id_are_the_same()
    {
        $generator = new HashidsTicketCodeGenerator('testsalt1');

        $code1 = $generator->generateFor(new Ticket(['id' => 1]));
        $code2 = $generator->generateFor(new Ticket(['id' => 1]));

        $this->assertEquals($code1, $code2);
    }

    /** @test */

    function codes_for_different_ticket_id_are_different()
    {
        $generator = new HashidsTicketCodeGenerator('testsalt1');

        $confirmationNumbers = array_map(function($i) use ($generator) {
            return $generator->generateFor(new Ticket(['id' => $i]));
        },range(1, 1000));

        $this->assertCount(1000, array_unique($confirmationNumbers));
    }


    /** @test */

    function codes_generated_with_different_salt_are_different()
    {
        $generator1 = new HashidsTicketCodeGenerator('testsalt1');
        $generator2 = new HashidsTicketCodeGenerator('testsalt2');

        $code1 = $generator1->generateFor(new Ticket(['id' => 1]));
        $code2 = $generator2->generateFor(new Ticket(['id' => 1]));

        $this->assertNotEquals($code1, $code2);
    }
}
