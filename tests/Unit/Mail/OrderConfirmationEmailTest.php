<?php

namespace Tests\Unit\Mail;

use App\Mail\OrderConfirmationEmail;
use App\Order;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderConfirmationEmailTest extends TestCase
{
    /** @test */
    
    function email_contains_link_to_order_confirmation_page()
    {
        $order = factory(Order::class)->make(['confiramtion_number' => 'ORDERCONFIRMATIONNUMBER123']);

        $email = new OrderConfirmationEmail($order);

        $rendered = $email->render();

        $this->assertContains(url('/orders/ORDERCONFIRMATIONNUMBER123'), $rendered);
    }

    /** @test */

    function email_has_a_subject()
    {
        $order = factory(Order::class)->make();

        $email = new OrderConfirmationEmail($order);

        $this->assertEquals('Your TicketBeast order.', $email->build()->subject);
    }
}
