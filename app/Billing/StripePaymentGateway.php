<?php

namespace App\Billing;

use Stripe\Error\InvalidRequest;

class StripePaymentGateway implements PaymentGatewayInterface
{
    protected $apiKey;
    const TEST_CARD_NUMBER = '4242424242424242';

    /**
     * StripePaymentGateway constructor.
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getValidTestToken($cardNumber = self::TEST_CARD_NUMBER)
    {
        return \Stripe\Token::create([
            "card" => [
                "number" => $cardNumber,
                "exp_month" => 1,
                "exp_year" => date('Y') + 1,
                "cvc" => "123"
            ]
        ], ['api_key' => $this->apiKey])->id;
    }

    public function newChargesDuring($callback)
    {
        $latestCharge = $this->lastCharge();

        $callback($this);

        return  $this->newChargesSince($latestCharge)->map(function($stripeCharge) {
            return new Charge([
                'amount' => $stripeCharge['amount'],
                'card_last_four' => $stripeCharge['source']['last4']
            ]);
        });
    }

    protected function lastCharge()
    {
        return array_first(\Stripe\Charge::all(
            ['limit' => 1],
            ['api_key' => $this->apiKey]
        )['data']);
    }

    /**
     * @return mixed
     */
    protected function newChargesSince($charge = null)
    {
        $newCharges = \Stripe\Charge::all(
            ['ending_before' => $charge ? $charge->id : null],
            ['api_key' =>  $this->apiKey]
        )['data'];

        return collect($newCharges);
    }

    public function charge($amount, $token, $account_id)
    {
        try {
            $stripeCharge = \Stripe\Charge::create([
                "amount" => $amount,
                "currency" => "usd",
                "source" => $token,
            ],['api_key' => $this->apiKey]);

            return new Charge([
                'amount' => $stripeCharge['amount'],
                'card_last_four' => $stripeCharge['source']['last4'],
                'destination' => $account_id
            ]);
        } catch (InvalidRequest $e) {
            throw new PaymentFailedException();
        }
    }
}