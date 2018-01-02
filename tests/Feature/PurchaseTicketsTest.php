<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGatewayInterface;
use App\Concert;
use App\Facades\OrderConfirmationNumber;
use App\Facades\TicketCode;
use App\Mail\OrderConfirmationEmail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTicketsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();

        Mail::fake();

        $this->paymentGateway = new FakePaymentGateway();

        $this->app->instance(PaymentGatewayInterface::class, $this->paymentGateway);
    }

    private function orderTickets($concert, $params)
    {
        return $this->json('POST', "concerts/{$concert->id}/orders", $params);
    }

    private function assertValidationError($key, $response) {
        $response->assertStatus(422);
        $this->assertArrayHasKey($key, $response->decodeResponseJson()['errors']);
    }

    /** @test */
    function customer_can_purchase_tickets_to_published_concert()
    {
        $this->withoutExceptionHandling();

        OrderConfirmationNumber::shouldReceive('generate')->andReturn('ORDERCONFIRMATIONCODE123');

        TicketCode::shouldReceive('generateFor')->andReturn('TICKETCODE1', 'TICKETCODE2', 'TICKETCODE3');

//        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3000])->addTickets(5);

        $concert = \ConcertFactory::createPublished(['ticket_price' => 3000, 'ticket_quantity' => 5]);


        $response = $this->orderTickets($concert, [
            'email' => 'example@email.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(201);

        $response->assertJsonFragment([
            'confirmation_number' => 'ORDERCONFIRMATIONCODE123',
            'email' => 'example@email.com',
            'amount' => 9000,
            'tickets' => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3']
            ]
        ]);

        $order = $concert->ordersFor('example@email.com')->first();

        $this->assertEquals(9000, $this->paymentGateway->totalCharge());
        $this->assertTrue($concert->hasOrderFor('example@email.com'));
        $this->assertEquals(3, $order->ticketsQuantity());

        Mail::assertSent(OrderConfirmationEmail::class, function($mail) use($order) {
            return $mail->hasTo('example@email.com') &&
                    $mail->order->id === $order->id;
        });
    }
    
    /** @test */
    
    function customer_cannot_purchase_tickets_to_unpublished_concert()
    {
        $concert = factory(Concert::class)->states('unpublished')->create()->addTickets(5);

        $response = $this->orderTickets($concert, [
            'email' => 'example@email.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(404);
        $this->assertFalse($concert->hasOrderFor('example@email.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharge());
    }
    
    /** @test */
    
    function an_order_is_not_created_if_payment_fails()
    {
        $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3000])->addTickets(5);

        $response = $this->orderTickets($concert, [
            'email' => 'example@email.com',
            'ticket_quantity' => 5,
            'payment_token' => 'invalid-token'
        ]);

        $response->assertStatus(422);
        $this->assertFalse($concert->hasOrderFor('example@email.com'));
        $this->assertEquals(5, $concert->ticketsRemaining());
    }
    
    /** @test */
    function email_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(5);

        $response = $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError('email', $response);
    }
    
    /** @test */
    
    function email_must_be_valid_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();

        $response = $this->orderTickets($concert, [
            'email' => 'invalid-email-address',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError('email', $response);
    }

    /** @test */
    function ticket_quantity_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();


        $response = $this->orderTickets($concert, [
            'email' => 'example@email.com',
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError('ticket_quantity', $response);
    }

    /** @test */
    function ticket_quantity_must_be_grater_then_zero_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();

        $response = $this->orderTickets($concert, [
            'email' => 'example@email.com',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError('ticket_quantity', $response);
    }

    /** @test */
    function payment_token_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();

        $response = $this->orderTickets($concert, [
            'email' => 'example@email.com',
            'ticket_quantity' => 2,
        ]);

        $this->assertValidationError('payment_token', $response);
    }
    
    /** @test */
    
    function cannot_purchase_more_tickets_then_remain()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(50);

        $response = $this->orderTickets($concert, [
            'email' => 'example@email.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(422);
        $this->assertFalse($concert->hasOrderFor('example@email.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharge());
    }
    
    /** @test */
    
    function cannot_purchase_tickets_another_customer_already_trying_to_purchase()
    {
        $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 2000])->addTickets(5);

        $this->paymentGateway->beforeFirstCharge(function($paymentGateway) use($concert){

            $response_b = $this->orderTickets($concert, [
                'email' => 'pearsonB@email.com',
                'ticket_quantity' => 1,
                'payment_token' => $this->paymentGateway->getValidTestToken()
            ]);

            $response_b->assertStatus(422);
            $this->assertFalse($concert->hasOrderFor('pearsonB@email.com'));
            $this->assertEquals(0, $this->paymentGateway->totalCharge());
        });

        $response_a = $this->orderTickets($concert, [
            'email' => 'pearsonA@email.com',
            'ticket_quantity' => 5,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response_a->assertStatus(201);
        $this->assertEquals(10000, $this->paymentGateway->totalCharge());
        $this->assertTrue($concert->hasOrderFor('pearsonA@email.com'));
        $this->assertEquals(5, $concert->ordersFor('pearsonA@email.com')->first()->ticketsQuantity());
    }
}
