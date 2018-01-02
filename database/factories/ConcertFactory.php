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

$factory->define(App\Concert::class, function (Faker $faker) {
    return [
        'user_id' => function() {
            return factory(App\User::class)->create()->id;
        },
        'title' => 'Example Band',
        'subtitle' => 'Example support bands',
        'date' => \Carbon\Carbon::parse('+2 weeks'),
        'ticket_price' => 3000,
        'ticket_quantity' => 20,
        'venue' => 'Example Theater',
        'venue_address' => 'Example Lane',
        'city' => 'Exampletown',
        'state' => 'CA',
        'zip' => '17916',
        'additional_information' => 'Example additional info.'
    ];
});

$factory->state(App\Concert::class, 'published', ['published_at' => \Carbon\Carbon::parse('-2 week')]);
$factory->state(App\Concert::class, 'unpublished', ['published_at' => null]);