<?php

namespace Tests\Unit;

use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Order;
use App\Ticket;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConcertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    
    function can_get_formatted_date()
    {
        $concert= factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00pm')
        ]);

        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }

    /** @test */

    function can_get_formatted_start_time()
    {
        $concert= factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 18:00:00')
        ]);

        $this->assertEquals('6:00pm', $concert->formatted_start_time);
    }

    /** @test */

    function can_get_ticket_price_in_dollars()
    {
        $concert= factory(Concert::class)->make([
            'ticket_price' => 2500
        ]);

        $this->assertEquals('25.00', $concert->ticket_price_in_dollars);
    }

    /** @test */

    function concert_knows_total_number_of_sold_tickets()
    {
        $concert= factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => null]));

        $this->assertEquals(3, $concert->ticketsSold());
    }
    
    /** @test */
    
    function concert_knows_total_number_of_available_tickets()
    {
        $concert = \ConcertFactory::createPublished(['ticket_quantity' => 5]);

        $this->assertEquals(5, $concert->ticketsTotal());
    }

    /** @test */

    function can_calculate_percentage_of_sold_out_tickets()
    {
        $concert= factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 14)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 9)->create(['order_id' => null]));

        $this->assertEquals(60.9, $concert->percentSoldOut());
    }

    /** @test */

    function calculating_a_revenue_in_dollars()
    {
        $concert= factory(Concert::class)->create();
        $orderA = factory(Order::class)->create(['amount' => 4150]);
        $orderB = factory(Order::class)->create(['amount' => 1350]);

        $concert->tickets()->saveMany(factory(Ticket::class, 14)->create(['order_id' => $orderA->id]));
        $concert->tickets()->saveMany(factory(Ticket::class, 9)->create(['order_id' => $orderB->id]));

        $this->assertEquals(55.00, $concert->revenueInDollars());
    }
    
    /** @test */
    
    function concert_with_set_published_date_are_published()
    {
        $publishedConcertA = factory(Concert::class)->states('published')->create();
        $publishedConcertB = factory(Concert::class)->states('published')->create();
        $unpublishedConcert = factory(Concert::class)->states('unpublished')->create();

        $publishedConcerts = Concert::published()->get();


        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    /** @test */

    function can_be_published()
    {
        $concert = factory(Concert::class)->states('unpublished')->create([
            'ticket_quantity' => 10
        ]);
        $this->assertFalse($concert->isPublished());
        $this->assertEquals(0, $concert->ticketsRemaining());

        $concert->publish();

        $this->assertTrue($concert->isPublished());
        $this->assertEquals(10, $concert->ticketsRemaining());
    }


    /** @test */

    function can_add_tickets()
    {
        $concert= factory(Concert::class)->create();

        $concert->addTickets(50);

        $this->assertEquals(50, $concert->ticketsRemaining());
    }
    
    /** @test */
    
    function tickets_remaining_does_not_include_tickets_associated_with_an_order()
    {
        $concert= factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => null]));

        $this->assertEquals(2, $concert->ticketsRemaining());
    }

    /** @test */

    function trying_to_reserve_more_tickets_then_available_throw_exception()
    {
        $concert= factory(Concert::class)->create()->addTickets(5);

        try {

            $concert->reserveTickets(6, 'example@test.com');

        } catch(NotEnoughTicketsException $e) {

            $this->assertFalse($concert->hasOrderFor('example@email.com'));
            $this->assertEquals(5, $concert->ticketsRemaining());

            return;
        }

        $this->fail('Order succeeded even though there were no enough tickets');
    }

    /** @test */
    
    function can_reserve_available_tickets()
    {
        $concert= factory(Concert::class)->create()->addTickets(5);
        $this->assertEquals(5, $concert->ticketsRemaining());

        $reservation = $concert->reserveTickets(3, 'example@test.com');

        $this->assertCount(3, $reservation->tickets());
        $this->assertEquals('example@test.com', $reservation->email());
        $this->assertEquals(2, $concert->ticketsRemaining());
    }
    
    /** @test */
    
    function cannot_reserve_tickets_that_already_have_been_purchased()
    {
        $concert= factory(Concert::class)->create()->addTickets(5);
        $order = factory(Order::class)->create();
        $order->tickets()->saveMany($concert->tickets->take(3));

        try {

            $concert->reserveTickets(3, 'jane@test.com');

        } catch(NotEnoughTicketsException $e) {

            $this->assertEquals(2, $concert->ticketsRemaining());

            return;
        }

        $this->fail('You have reserved already purchased tickets');
    }

    /** @test */

    function cannot_reserve_tickets_that_already_have_been_reserved()
    {
        $concert= factory(Concert::class)->create()->addTickets(5);

        $concert->reserveTickets(3, 'jane@test.com');

        try {

            $concert->reserveTickets(4, 'john@test.com');

        } catch(NotEnoughTicketsException $e) {

            $this->assertEquals(2, $concert->ticketsRemaining());

            return;
        }

        $this->fail('You have tried to reserve already reserved tickets');
    }
}
