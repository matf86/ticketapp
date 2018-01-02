<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    protected function getPaymentGateway()
    {
        return new FakePaymentGateway();
    }

    /** @test */
    
    function running_a_hook_before_first_charge()
    {
        $paymentGateway = new FakePaymentGateway();
        $timesCallbackRun = 0;

        $paymentGateway->beforeFirstCharge(function($paymentGateway) use (&$timesCallbackRun) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            $timesCallbackRun++;

            $this->assertEquals(2500, $paymentGateway->totalCharge());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        $this->assertEquals(1, $timesCallbackRun);
        $this->assertEquals(5000, $paymentGateway->totalCharge());
    }
}
