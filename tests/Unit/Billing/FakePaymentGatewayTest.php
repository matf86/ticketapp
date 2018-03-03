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
    
    function can_get_total_charges_for_a_specific_account()
    {
        $paymentGateway = new FakePaymentGateway();

        $paymentGateway->charge(1000, $paymentGateway->getValidTestToken(), 'test_account_00');
        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken(), 'test_account_01');
        $paymentGateway->charge(1500, $paymentGateway->getValidTestToken(), 'test_account_01');

        $this->assertEquals(3500, $paymentGateway->totalChargesFor('test_account_01'));
    }

    /** @test */
    
    function running_a_hook_before_first_charge()
    {
        $paymentGateway = new FakePaymentGateway();
        $timesCallbackRun = 0;

        $paymentGateway->beforeFirstCharge(function($paymentGateway) use (&$timesCallbackRun) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_account_00');
            $timesCallbackRun++;

            $this->assertEquals(2500, $paymentGateway->totalCharge());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_account_00');
        $this->assertEquals(1, $timesCallbackRun);
        $this->assertEquals(5000, $paymentGateway->totalCharge());
    }
}
