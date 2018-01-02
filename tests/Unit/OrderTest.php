<?php

namespace Tests\Unit;

use App\Billing\Charge;
use App\Concert;
use App\Order;
use App\OrderConfirmationNumberGeneratorInterface;
use App\Reservation;
use App\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    
    function creating_an_order_from_tickets_email_and_charge()
    {
        $charge = new Charge([
            'amount' => 3000,
            'card_last_four' => '4242'
        ]);
        $tickets = collect([
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
        ]);

        $order = Order::forTickets('test@example.com', $tickets, $charge);

        $this->assertEquals('test@example.com', $order->email);
        $this->assertEquals(3000, $order->amount);
        $this->assertEquals('4242', $order->card_last_four);
        $tickets->each->shouldHaveReceived('claimFor',  [$order]);
    }
    
    /** @test */
    
    function converting_to_an_array()
    {
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATIONCODE123',
            'email' => 'test@example.com',
            'amount' => 5000
        ]);

        $order->tickets()->saveMany([
            factory(Ticket::class)->create(['code' => 'TICKETCODE1']),
            factory(Ticket::class)->create(['code' => 'TICKETCODE2']),
            factory(Ticket::class)->create(['code' => 'TICKETCODE3']),
        ]);

        $result = $order->toArray();

        $this->assertEquals([
            'confirmation_number' => 'ORDERCONFIRMATIONCODE123',
            'email' => 'test@example.com',
            'amount' => 5000,
            'tickets' => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3'],
            ]
        ], $result);
    }

    /** @test */
    
    function retrieving_an_order_by_confirmation_number()
    {
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION12345'
        ]);

        $foundOrder = Order::findByConfirmationNumber('ORDERCONFIRMATION12345');

        $this->assertEquals($order->id, $foundOrder->id);
    }

    /** @test */

    function retrieving_nonexistent_order_throw_an_exception()
    {
        try {
            Order::findByConfirmationNumber('WRONG_NUMBER');
        } catch (ModelNotFoundException $e) {
            $this->assertEquals('No query results for model [App\Order].', $e->getMessage());
            return;
        }

        $this->fail('Error,required exception have not been thrown.');
    }
}
