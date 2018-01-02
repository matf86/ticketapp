<?php

namespace Tests\Feature\Backstage;

use App\User;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PromotorLogingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    
    function logging_in_with_valid_credentials()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create([
            'email' => 'user@example.com',
            'password' => bcrypt('secret')
        ]);

        $response = $this->post('/login', ['email' => 'user@example.com', 'password' => 'secret']);

        $response->assertRedirect('/backstage/concerts/new');
        $this->assertAuthenticatedAs($user);
    }


    /** @test */

    function logging_in_with_invalid_credentials()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create([
            'email' => 'user@example.com',
            'password' => bcrypt('secret')
        ]);

        $response = $this->post('/login', ['email' => 'user@example.com', 'password' => 'wrong_password']);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertFalse(Auth::check());
    }

    /** @test */

    function logging_in_with_account_that_does_not_exist()
    {
        $this->withoutExceptionHandling();

        $response = $this->post('/login', ['email' => 'user@example.com', 'password' => 'some_password']);

        $response->assertRedirect('/login');

        $response->assertSessionHasErrors('email');

        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));

        $this->assertFalse(Auth::check());
    }
    
    /** @test */
    
    function user_can_log_out()
    {
        Auth::login(factory(User::class)->create());

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertFalse(Auth::check());
    }
}
