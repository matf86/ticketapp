<?php

namespace Tests\Unit;

use App\Billing\FakePaymentGateway;
use App\Concert;
use App\Reservation;
use App\Ticket;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    
    function calculating_the_total_cost()
    {
        $tickets = collect([
            (object) ['price' => 1500],
            (object) ['price' => 1500],
            (object) ['price' => 1500],
            (object) ['price' => 1500],
            (object) ['price' => 1500],
        ]);

        $reservation = new Reservation($tickets, 'exampl@test.com');

        $this->assertEquals(7500, $reservation->totalCost());
    }
    
    /** @test */
    
    function completing_a_reservation()
    {
        $concert = factory(Concert::class)->create(['ticket_price' => 1000]);
        $tickets = factory(Ticket::class, 3)->create(['concert_id' => $concert->id]);
        $reservation = new Reservation($tickets, 'example@test.com');
        $paymentGateway = new FakePaymentGateway();

        $order = $reservation->complete($paymentGateway, $paymentGateway->getValidTestToken(), 'test_account_001');

        $this->assertEquals('example@test.com', $order->email);
        $this->assertEquals(3, $order->ticketsQuantity());
        $this->assertEquals(3000, $order->amount);
        $this->assertEquals(3000, $paymentGateway->totalChargesFor('test_account_001'));
    }
    
    /** @test */
    
    function reserved_tickets_are_released_when_reservation_is_canceled()
    {
        $tickets = collect([
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
        ]);

        $reservation = new Reservation($tickets, 'exampl@test.com');

        $reservation->cancel();

        foreach ($tickets as $ticket) {
            $ticket->shouldHaveReceived('release')->once();
        }
    }
    
    /** @test */
    
    function retrieving_the_reservation_tickets()
    {
        $tickets = collect([
            (object) ['price' => 1500],
            (object) ['price' => 1500],
            (object) ['price' => 1500],
        ]);

        $reservation = new Reservation($tickets, 'exampl@test.com');

        $this->assertEquals($tickets, $reservation->tickets());
    }

    /** @test */

    function retrieving_the_customer_email()
    {
        $tickets = collect();

        $reservation = new Reservation($tickets, 'exampl@test.com');

        $this->assertEquals('exampl@test.com', $reservation->email());
    }
}
