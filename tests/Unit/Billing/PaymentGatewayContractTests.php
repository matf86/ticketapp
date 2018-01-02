<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;

trait PaymentGatewayContractTests
{
    abstract protected function getPaymentGateway();

    /** @test */

    function charges_with_valid_payment_token_are_successful()
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway)  {
            $paymentGateway->charge(3000, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(1, $newCharges);
        $this->assertEquals(3000, $newCharges->map->amount()->sum());
    }

    /** @test */

    function can_get_details_about_successful_charge()
    {
        $paymentGateway = $this->getPaymentGateway();

        $charge = $paymentGateway->charge(3000, $paymentGateway->getValidTestToken($paymentGateway::TEST_CARD_NUMBER));

        $this->assertEquals(substr($paymentGateway::TEST_CARD_NUMBER, -4), $charge->cardLastFour());
        $this->assertEquals(3000, $charge->amount());

    }

    /** @test */

    function charges_with_invalid_payment_token_fails()
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway) {
            try {
                $paymentGateway->charge(2500, 'invalid-token');
            } catch (PaymentFailedException $e) {
                return;
            }
            $this->fail('Charge with invalid payment token did not throw PaymentFailedException');
        });

        $this->assertCount(0, $newCharges);
    }

    /** @test */

    function can_fetch_charges_created_during_callback()
    {
        $paymentGateway = $this->getPaymentGateway();

        $paymentGateway->charge(1000, $paymentGateway->getValidTestToken());
        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken());

        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway)  {
            $paymentGateway->charge(3000, $paymentGateway->getValidTestToken());
            $paymentGateway->charge(4000, $paymentGateway->getValidTestToken());
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(3, $newCharges);
        $this->assertEquals([5000,4000,3000], $newCharges->map->amount()->all());
        $this->assertEquals(12000, $newCharges->map->amount()->sum());
    }
}
