<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddConcertTest extends TestCase
{
    use RefreshDatabase;

    protected function validParams($overrides = []) {
        return array_merge([
            'title' => 'New Band',
            'subtitle' => 'Completely new support bands',
            'date' => '2018-01-12',
            'time' => '9:00pm',
            'ticket_price' => '15.00',
            'ticket_quantity' => '50',
            'venue' => 'test Theater',
            'venue_address' => 'Example Lane 10',
            'city' => 'Testtown',
            'state' => 'CA',
            'zip' => '17916',
            'additional_information' => 'Example additional info.'
        ], $overrides);
    }

    /** @test */

    function promoters_can_view_add_concert_form()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/backstage/concerts/new');

        $response->assertStatus(200);
    }


    /** @test */

    function guests_cannot_view_add_concert_form()
    {
        $response = $this->get('/backstage/concerts/new');
        
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
    
    /** @test */
    
    function promoter_can_add_valid_concert()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts',  [
            'title' => 'New Band',
            'subtitle' => 'Completely new support bands',
            'date' => '2018-01-12',
            'time' => '9:00pm',
            'ticket_price' => '15.00',
            'ticket_quantity' => '50',
            'venue' => 'test Theater',
            'venue_address' => 'Example Lane 10',
            'city' => 'Testtown',
            'state' => 'CA',
            'zip' => '17916',
            'additional_information' => 'Example additional info.'
        ]);

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/concerts/{$concert->id}");

        $this->assertTrue($concert->user->is($user));

        $this->assertTrue($concert->isPublished());

        $this->assertEquals('New Band', $concert->title);
        $this->assertEquals('Completely new support bands', $concert->subtitle);
        $this->assertEquals( 'Example additional info.', $concert->additional_information);
        $this->assertEquals( Carbon::parse('2018-01-12 9:00pm'), $concert->date);
        $this->assertEquals( 'test Theater', $concert->venue);
        $this->assertEquals( 'Example Lane 10', $concert->venue_address);
        $this->assertEquals( 'Testtown', $concert->city);
        $this->assertEquals( 'CA', $concert->state);
        $this->assertEquals( '17916', $concert->zip);
        $this->assertEquals( 1500, $concert->ticket_price);
        $this->assertEquals( 50, $concert->ticket_quantity);
        $this->assertEquals( 50, $concert->ticketsRemaining());
    }

    /** @test */

    function guest_cannot_add_concert()
    {
        $response = $this->post('/backstage/concerts', $this->validParams());

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/login");

        $this->assertNull($concert);
    }

    /** @test */

    function title_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'title' => '',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('title');
        $this->assertNull($concert);
    }

    /** @test */

    function subtitle_is_optional()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts',  $this->validParams([
            'subtitle' => '',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/concerts/{$concert->id}");
        $this->assertNull($concert->subtitle);
    }

    /** @test */

    function additional_information_is_optional()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts',  $this->validParams([
            'additional_information' => ''
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/concerts/{$concert->id}");
        $this->assertNull($concert->additional_information);
    }

    /** @test */

    function date_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'date' => '',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('date');
        $this->assertNull($concert);
    }

    /** @test */

    function date_must_be_a_valid_date()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'date' => 'not-a-date',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('date');
        $this->assertNull($concert);
    }

    /** @test */

    function time_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'time' => '',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('time');
        $this->assertNull($concert);
    }

    /** @test */

    function time_must_be_in_valid_format()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'time' => 'not-valid-time-format',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('time');
        $this->assertNull($concert);
    }

    /** @test */

    function venue_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'venue' => '',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('venue');
        $this->assertNull($concert);
    }

    /** @test */

    function venue_address_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'venue_address' => '',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('venue_address');
        $this->assertNull($concert);
    }

    /** @test */

    function city__is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'city' => '',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('city');
        $this->assertNull($concert);
    }

    /** @test */

    function state_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'state' => '',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('state');
        $this->assertNull($concert);
    }

    /** @test */

    function zip_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'zip' => '',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('zip');
        $this->assertNull($concert);
    }

    /** @test */

    function ticket_price_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'ticket_price' => '',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_price');
        $this->assertNull($concert);
    }

    /** @test */

    function ticket_price_must_be_numeric()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'ticket_price' => 'invalid-type',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_price');
        $this->assertNull($concert);
    }

    /** @test */

    function ticket_price_must_be_at_least_5()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'ticket_price' => '4.99',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_price');
        $this->assertNull($concert);
    }

    /** @test */

    function ticket_quantity_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'ticket_quantity' => '',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertNull($concert);
    }

    /** @test */

    function ticket_quantity_must_be_numeric()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'ticket_quantity' => 'invalid-type',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertNull($concert);
    }

    /** @test */

    function ticket_quantity_must_be_at_least_1()
    {
        $user = factory(User::class)->create();

        $response = $this->from("/backstage/concerts/new")->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'ticket_quantity' => '0',
        ]));

        $concert = Concert::first();

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertNull($concert);
    }

}
