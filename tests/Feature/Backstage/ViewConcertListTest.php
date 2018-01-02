<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewConcertListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();

        Collection::macro('assertContains', function($value) {
            \PHPUnit\Framework\Assert::assertTrue($this->contains($value), 'Failed to assert that collection contains specified value.');
        });

        Collection::macro('assertNotContains', function($value) {
            \PHPUnit\Framework\Assert::assertFalse($this->contains($value), 'Failed to assert that collection do not contain specified value.');
        });
    }

    /** @test */
    
    function guests_cannot_see_promoters_concerts_list()
    {
        $response = $this->get('/backstage/concerts');

        $response->assertRedirect('/login');
        $response->setStatusCode(302);
    }

    /** @test */

    function promoter_can_see__only_his_own_concerts()
    {
        $userA = factory(User::class)->create();
        $userB = factory(User::class)->create();

        $concertA = factory(Concert::class)->create(['user_id' => $userA->id]);
        $concertB = factory(Concert::class)->create(['user_id' => $userA->id]);
        $concertC = factory(Concert::class)->create(['user_id' => $userB->id]);
        $concertD = factory(Concert::class)->create(['user_id' => $userA->id]);

        $response = $this->actingAs($userA)->get('/backstage/concerts');

        $response->setStatusCode(200);

        $response->data('concerts')->assertContains($concertA);
        $response->data('concerts')->assertContains($concertB);
        $response->data('concerts')->assertContains($concertD);
        $response->data('concerts')->assertNotContains($concertC);
    }

//    /** @test */
//
//    function promoter_can_see_his_concerts_list()
//    {
//        $user = factory(User::class)->create();
//        $concerts = factory(Concert::class, 3)->create(['user_id' => $user->id]);
//
//        $response = $this->actingAs($user)->get('/backstage/concerts');
//
//        $response->setStatusCode(200);
//
//        $this->assertTrue($response->getOriginalContent()->getData()['concerts']->contains($concerts[0]));
//        $this->assertTrue($response->getOriginalContent()->getData()['concerts']->contains($concerts[1]));
//        $this->assertTrue($response->getOriginalContent()->getData()['concerts']->contains($concerts[2]));
//
//    }
}
