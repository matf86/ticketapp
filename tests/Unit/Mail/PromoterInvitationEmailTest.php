<?php

namespace Tests\Feature;

use App\Invitation;
use App\Mail\PromoterInvitationEmail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PromoterInvitationEmailTest extends TestCase
{
    /** @test */

    function email_contains_link_to_register_page()
    {
        $invitation = factory(Invitation::class)->make(['code' => 'INVITATIONCODE']);

        $email = new PromoterInvitationEmail($invitation);

        $rendered = $email->render();

        $this->assertContains(url("/invitations/{$invitation->code}"), $rendered);
    }

    /** @test */

    function email_has_a_subject()
    {
        $invitation = factory(Invitation::class)->make(['code' => 'INVITATIONCODE']);

        $email = new PromoterInvitationEmail($invitation);

        $this->assertEquals('Please welcome our invitation to Ticketbeast.', $email->build()->subject);
    }
}
