<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Order::class, function (Faker $faker) {
    return [
        'email' => 'test@example.com',
        'amount' => 3500,
        'confirmation_number' => 'ORDERCONFIRMATIONNUMBER1234',
        'card_last_four' => '4242'
    ];
});
