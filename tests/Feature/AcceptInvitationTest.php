<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Invitation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    
    function viewing_an_unused_invitation()
    {
        $this->withoutExceptionHandling();

        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE'
        ]);

        $response = $this->get('/invitations/TESTCODE');

        $response->assertStatus(200);
        $response->assertViewIs('invitations.show');
        $this->assertTrue($response->data('invitation')->is($invitation));
    }

    /** @test */

    function viewing_an_used_invitation()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $invitation = factory(Invitation::class)->create([
            'code' => 'TESTCODE',
            'user_id' => $user->id
        ]);

        $response = $this->get('/invitations/TESTCODE');

        $response->assertStatus(404);
    }

    /** @test */

    function viewing_an_invalid_invitation_return_404()
    {
        $response = $this->get('/invitations/TESTCODE');

        $response->assertStatus(404);
    }

    /** @test */

    function registering_with_valid_invitation_code()
    {
        $this->withoutExceptionHandling();

        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE'
        ]);

        $response = $this->post('/register', [
            'email' =>'test@example.com',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE'
        ]);

        $response->assertRedirect('/backstage/concerts');

        $this->assertEquals(1, User::count());

        $user =  User::first();
        $this->assertAuthenticatedAs($user);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertTrue(Hash::check('secret', $user->password));

        $this->assertNotNull($invitation->fresh()->user_id);
        $this->assertTrue($invitation->fresh()->user->is($user));
    }

    /** @test */

    function registering_with_already_used_invitation_code_fails()
    {
        $user = factory(User::class)->create();

        factory(Invitation::class)->create([
            'user_id' => $user->id,
            'code' => 'TESTCODE'
        ]);

        $response = $this->post('/register', [
            'email' =>'test@example.com',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE'
        ]);

        $response->assertStatus(404);
        $this->assertEquals(1, User::count());
    }

    /** @test */

    function registering_with_invitation_code_that_does_not_exists_fails()
    {
        $response = $this->post('/register', [
            'email' =>'test@example.com',
            'password' => 'secret',
            'invitation_code' => 'INVALID-TEST-CODE'
        ]);

        $response->assertStatus(404);
        $this->assertEquals(0, User::count());
    }

    /** @test */

    function email_is_required_when_registering()
    {
       $invitation =  factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE'
        ]);

        $response = $this->from("/invitations/{$invitation->code}")->post('/register', [
            'email' =>'',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE'
        ]);

        $response->assertRedirect("/invitations/{$invitation->code}");
        $response->assertSessionHasErrors('email');
        $this->assertEquals(0, User::count());
    }

    /** @test */

    function valid_email_address_is_required_when_registering()
    {
        $invitation =  factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE'
        ]);

        $response = $this->from("/invitations/{$invitation->code}")->post('/register', [
            'email' => 'invalid-string',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE'
        ]);

        $response->assertRedirect("/invitations/{$invitation->code}");
        $response->assertSessionHasErrors('email');
        $this->assertEquals(0, User::count());
    }

    /** @test */

    function email_address_must_be_unique_required_when_registering()
    {
        factory(User::class)->create([
            'email' => 'example@test.com'
        ]);

        $invitation =  factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE'
        ]);

        $response = $this->from("/invitations/{$invitation->code}")->post('/register', [
            'email' => 'example@test.com',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE'
        ]);

        $response->assertRedirect("/invitations/{$invitation->code}");
        $response->assertSessionHasErrors('email');
        $this->assertEquals(1, User::count());
    }

    /** @test */
    function password_is_required_when_registering()
    {
        $invitation =  factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE'
        ]);

        $response = $this->from("/invitations/{$invitation->code}")->post('/register', [
            'email' =>'test@example.com',
            'password' => '',
            'invitation_code' => 'TESTCODE'
        ]);

        $response->assertRedirect("/invitations/{$invitation->code}");
        $response->assertSessionHasErrors('password');
        $this->assertEquals(0, User::count());
    }
}
