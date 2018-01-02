<?php

namespace Tests\Feature;

use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewConcertListingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    
    function user_can_see_published_concert_listing()
    {
        $concert = factory(Concert::class)->states('published')->create([
            'title' => 'Cool Band',
            'subtitle' => 'Not so famous band',
            'date' => Carbon::parse('December 12, 2016 8:00pm'),
            'ticket_price' => 3250,
            'venue' => 'Mosh pit',
            'venue_address' => 'Example Lane',
            'city' => 'Exampletown',
            'state' => 'CA',
            'zip' => '17916',
            'additional_information' => 'For tickets call (555) 555-5555',
        ]);

        $response = $this->get('concerts/'.$concert->id);

        $response->assertSee('Cool Band');
        $response->assertSee('Not so famous band');
        $response->assertSee('December 12, 2016');
        $response->assertSee('8:00pm');
        $response->assertSee('32.50');
        $response->assertSee('Mosh pit');
        $response->assertSee('Example Lane');
        $response->assertSee('Exampletown');
        $response->assertSee('CA');
        $response->assertSee('17916');
        $response->assertSee('For tickets call (555) 555-5555');

    }

    /** @test */

    function user_cannot_view_unpublished_concert_listing()
    {
        $concert = factory(Concert::class)->states('unpublished')->create();

        $response = $this->get('concerts/'.$concert->id);

        $response->assertStatus(404);
    }

}
