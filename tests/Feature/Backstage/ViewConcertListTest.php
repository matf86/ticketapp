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

    /** @test */

    function guests_cannot_see_promoters_concerts_list()
    {
        $response = $this->get('/backstage/concerts');

        $response->assertRedirect('/login');
        $response->setStatusCode(302);
    }

    /** @test */

    function promoter_can_see_only_his_own_concerts()
    {
        $userA = factory(User::class)->create();
        $userB = factory(User::class)->create();

        $publishedConcertA = \ConcertFactory::createPublished(['user_id' => $userA->id]);
        $publishedConcertB = \ConcertFactory::createPublished(['user_id' => $userA->id]);
        $publishedConcertC = \ConcertFactory::createPublished(['user_id' => $userB->id]);

        $unpublishedConcertA = \ConcertFactory::createUnpublished(['user_id' => $userA->id]);
        $unpublishedConcertB = \ConcertFactory::createUnpublished(['user_id' => $userA->id]);
        $unpublishedConcertC = \ConcertFactory::createUnpublished(['user_id' => $userB->id]);

        $response = $this->actingAs($userA)->get('/backstage/concerts');

        $response->setStatusCode(200);

        $response->data('publishedConcerts')->assertEquals([
            $publishedConcertA, $publishedConcertB
        ]);

        $response->data('unpublishedConcerts')->assertEquals([
            $unpublishedConcertA, $unpublishedConcertB
        ]);
    }
}
