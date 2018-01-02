<?php

namespace Tests\Browser;

use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PromoterLoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function logging_in_successful()
    {
        $user = factory(User::class)->create([
            'email' => 'user@example.com',
            'password' => bcrypt('secret')
        ]);

        $this->browse(function (Browser $browser) {
            dd($browser->visit('/'));

        });
    }
}
