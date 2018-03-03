<?php

namespace Tests\Feature;

use App\Invitation;
use App\Mail\PromoterInvitationEmail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Facades\InvitationCode;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitePromoterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */

    function inviting_a_promoter_via_cli()
    {
        Mail::fake();

        InvitationCode::shouldReceive('generate')->andReturn('TESTCODE');

        $this->artisan('invite-promoter', ['email' => 'example@test.com']);

        $this->assertEquals(1, Invitation::count());
        $invitation = Invitation::first();

        $this->assertEquals('example@test.com', $invitation->email);
        $this->assertEquals('TESTCODE', $invitation->code);

        Mail::assertSent(PromoterInvitationEmail::class, function($mail) use ($invitation) {
            return $mail->hasTo('example@test.com') &&
                $mail->invitation->is($invitation);
        });
    }
}
