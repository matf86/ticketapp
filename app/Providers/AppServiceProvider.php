<?php

namespace App\Providers;

use App\Billing\PaymentGatewayInterface;
use App\Billing\StripePaymentGateway;
use App\InvitationCodeGeneratorInterface;
use App\OrderConfirmationNumberGeneratorInterface;
use App\RandomConfirmationNumberGenerator;
use App\HashidsTicketCodeGenerator;
use App\TicketCodeGeneratorInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(StripePaymentGateway::class, function() {
           return new StripePaymentGateway(config('services.stripe.secret'));
        });

        $this->app->bind(PaymentGatewayInterface::class, StripePaymentGateway::class);

        $this->app->bind(OrderConfirmationNumberGeneratorInterface::class, RandomConfirmationNumberGenerator::class);

        $this->app->bind(InvitationCodeGeneratorInterface::class, RandomConfirmationNumberGenerator::class);

        $this->app->bind(HashidsTicketCodeGenerator::class, function() {
           return new HashidsTicketCodeGenerator(config('app.ticket_code_salt'));
        });

        $this->app->bind(TicketCodeGeneratorInterface::class, HashidsTicketCodeGenerator::class);
    }
}
