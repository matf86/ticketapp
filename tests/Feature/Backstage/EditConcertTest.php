<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EditConcertTest extends TestCase
{
    use RefreshDatabase;

    private function oldAttributes($overrides = [])
    {
        return array_merge([
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2018-01-10 12:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old venue address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 3000,
            'ticket_quantity' => 20
        ], $overrides);
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2019-12-10',
            'time' => '9:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New venue address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99210',
            'ticket_price' => '65.00',
            'ticket_quantity' => '35'
        ], $overrides);
    }

    /** @test */
    function promoters_can_view_the_edit_form_for_their_own_unpublished_concerts()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(200);
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    function promoters_cannot_view_the_edit_form_for_their_own_published_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user->id]);
        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(403);
    }

    /** @test */
    function promoters_cannot_view_the_edit_form_for_other_concerts()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(404);
    }

    /** @test */
    function promoters_see_a_404_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get("/backstage/concerts/999/edit");

        $response->assertStatus(404);
    }

    /** @test */
    function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_any_concert()
    {
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $response = $this->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist()
    {
        $response = $this->get("/backstage/concerts/999/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
    
    /** @test */
    
    function promoters_can_edit_their_own_unpublished_concerts()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id' => $user->id
        ]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2019-12-10',
            'time' => '9:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New venue address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99210',
            'ticket_price' => '65.00',
            'ticket_quantity' => '35'
        ]);

        $response->assertRedirect('/backstage/concerts');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals( 'New title', $concert->title);
            $this->assertEquals( 'New subtitle', $concert->subtitle);
            $this->assertEquals( 'New additional information', $concert->additional_information);
            $this->assertEquals( Carbon::parse('2019-12-10 9:00pm'), $concert->date);
            $this->assertEquals( 'New venue', $concert->venue);
            $this->assertEquals( 'New venue address', $concert->venue_address);
            $this->assertEquals( 'New city', $concert->city);
            $this->assertEquals( 'New state', $concert->state);
            $this->assertEquals( '99210', $concert->zip);
            $this->assertEquals( 6500, $concert->ticket_price);
            $this->assertEquals( 35, $concert->ticket_quantity);
        });
    }

    /** @test */

    function promoters_can_not_edit_other_users_unpublished_concerts()
    {

        $userA = factory(User::class)->create();
        $userB = factory(User::class)->create();

        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id' => $userB->id,
        ]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($userA)->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertStatus(404);

        $this->assertArraySubset($this->oldAttributes([
            'user_id' => $userB->id
        ]),$concert->fresh()->getAttributes());
    }

    /** @test */

    function promoters_can_not_edit_their_own_published_concerts()
    {

        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->states('published')->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertStatus(403);

        $this->assertArraySubset($this->oldAttributes([
            'user_id' => $user->id
        ]),$concert->fresh()->getAttributes());
    }

    /** @test */

    function guest_can_not_edit_published_concerts()
    {

        $concert = factory(Concert::class)->create($this->oldAttributes());

        $this->assertFalse($concert->isPublished());

        $response = $this->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertStatus(302);
        $response->assertRedirect('/login');

        $this->assertArraySubset($this->oldAttributes(),$concert->fresh()->getAttributes());
    }

    /** @test */

    function title_is_required_when_promoter_is_trying_to_update_concert_data()
    {
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2018-01-10 12:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old venue address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 3000
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", [
            'title' => '',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2019-12-10',
            'time' => '9:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New venue address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99210',
            'ticket_price' => '65.00'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/{$concert->id}/edit");
        $response->assertSessionHasErrors('title');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals( 'Old title', $concert->title);
            $this->assertEquals( 'Old subtitle', $concert->subtitle);
            $this->assertEquals( 'Old additional information', $concert->additional_information);
            $this->assertEquals( Carbon::parse('2018-01-10 12:00pm'), $concert->date);
            $this->assertEquals( 'Old venue', $concert->venue);
            $this->assertEquals( 'Old venue address', $concert->venue_address);
            $this->assertEquals( 'Old city', $concert->city);
            $this->assertEquals( 'Old state', $concert->state);
            $this->assertEquals( '00000', $concert->zip);
            $this->assertEquals( 3000, $concert->ticket_price);

        });
    }

    /** @test */

    function ticket_quantity_is_required_when_promoter_is_trying_to_update_concert_data()
    {
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2018-01-10 12:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old venue address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 3000,
            'ticket_quantity' => 10
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2019-12-10',
            'time' => '9:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New venue address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99210',
            'ticket_price' => '65.00',
            'ticket_quantity' => ''
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals( 'Old title', $concert->title);
            $this->assertEquals( 'Old subtitle', $concert->subtitle);
            $this->assertEquals( 'Old additional information', $concert->additional_information);
            $this->assertEquals( Carbon::parse('2018-01-10 12:00pm'), $concert->date);
            $this->assertEquals( 'Old venue', $concert->venue);
            $this->assertEquals( 'Old venue address', $concert->venue_address);
            $this->assertEquals( 'Old city', $concert->city);
            $this->assertEquals( 'Old state', $concert->state);
            $this->assertEquals( '00000', $concert->zip);
            $this->assertEquals( 3000, $concert->ticket_price);
            $this->assertEquals( 10, $concert->ticket_quantity);
        });
    }

    /** @test */

    function ticket_quantity_must_be_integer_when_promoter_is_trying_to_update_concert_data()
    {
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2018-01-10 12:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old venue address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 3000,
            'ticket_quantity' => 10
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2019-12-10',
            'time' => '9:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New venue address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99210',
            'ticket_price' => '65.00',
            'ticket_quantity' => '7.5'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals( 'Old title', $concert->title);
            $this->assertEquals( 'Old subtitle', $concert->subtitle);
            $this->assertEquals( 'Old additional information', $concert->additional_information);
            $this->assertEquals( Carbon::parse('2018-01-10 12:00pm'), $concert->date);
            $this->assertEquals( 'Old venue', $concert->venue);
            $this->assertEquals( 'Old venue address', $concert->venue_address);
            $this->assertEquals( 'Old city', $concert->city);
            $this->assertEquals( 'Old state', $concert->state);
            $this->assertEquals( '00000', $concert->zip);
            $this->assertEquals( 3000, $concert->ticket_price);
            $this->assertEquals( 10, $concert->ticket_quantity);
        });
    }


    /** @test */

    function ticket_quantity_must_be_at_least_1_when_promoter_is_trying_to_update_concert_data()
    {
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2018-01-10 12:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old venue address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 3000,
            'ticket_quantity' => 10
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2019-12-10',
            'time' => '9:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New venue address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99210',
            'ticket_price' => '65.00',
            'ticket_quantity' => '0'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');

        tap($concert->fresh(), function($concert) {
            $this->assertEquals( 'Old title', $concert->title);
            $this->assertEquals( 'Old subtitle', $concert->subtitle);
            $this->assertEquals( 'Old additional information', $concert->additional_information);
            $this->assertEquals( Carbon::parse('2018-01-10 12:00pm'), $concert->date);
            $this->assertEquals( 'Old venue', $concert->venue);
            $this->assertEquals( 'Old venue address', $concert->venue_address);
            $this->assertEquals( 'Old city', $concert->city);
            $this->assertEquals( 'Old state', $concert->state);
            $this->assertEquals( '00000', $concert->zip);
            $this->assertEquals( 3000, $concert->ticket_price);
            $this->assertEquals( 10, $concert->ticket_quantity);
        });
    }

}
