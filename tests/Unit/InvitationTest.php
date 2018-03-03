<?php

namespace Tests\Unit;

use App\Invitation;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;
    /** @test */

    function invitation_can_be_found_by_code()
    {
        $invitation = factory(Invitation::class)->create([
            'code' => 'TESTCODE'
        ]);

        $this->assertEquals($invitation->id, $invitation::findByCode('TESTCODE')->id);
    }
}
