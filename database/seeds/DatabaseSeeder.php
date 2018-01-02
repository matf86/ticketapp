<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Concert::class)->states('published')->create([
                'title' => 'Example Band',
                'subtitle' => 'Example support bands',
                'date' => \Carbon\Carbon::parse('+2 weeks'),
                'ticket_price' => 3000,
                'venue' => 'Example Theater',
                'venue_address' => 'Example Lane',
                'city' => 'Exampletown',
                'state' => 'CA',
                'zip' => '17916',
                'additional_information' => 'Example additional info.'
        ])->addTickets(10);
    }
}
