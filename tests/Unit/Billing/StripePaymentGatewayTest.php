<?php

namespace Tests\Unit\Billing;

use App\Billing\StripePaymentGateway;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


/**
 *@group integration
 */

class StripePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    protected function setUp()
    {
        parent::setUp();
    }

    protected function getPaymentGateway()
    {
        return new StripePaymentGateway(config('services.stripe.secret'));
    }
}
