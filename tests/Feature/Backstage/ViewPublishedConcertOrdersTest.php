<?php

namespace Tests\Feature\Backstage;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewPublishedConcertOrdersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_promoter_can_view_the_orders_of_their_own_published_concert()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = \ConcertFactory::createPublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.published-concert-orders.index');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    function a_promoter_can_view_10_most_recent_orders_for_their_published_concert()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = \ConcertFactory::createPublished(['user_id' => $user->id]);

        $oldOrder = \OrderFactory::createForConcert($concert, 1, ['created_at' => Carbon::parse('11 days ago')]);

        $order1 = \OrderFactory::createForConcert($concert, 1, ['created_at' => Carbon::parse('10 days ago')]);
        $order2= \OrderFactory::createForConcert($concert,  1, ['created_at' => Carbon::parse('9 days ago')]);
        $order3 = \OrderFactory::createForConcert($concert, 1, ['created_at' => Carbon::parse('8 days ago')]);
        $order4 = \OrderFactory::createForConcert($concert, 1, ['created_at' => Carbon::parse('7 days ago')]);
        $order5 = \OrderFactory::createForConcert($concert, 1, ['created_at' => Carbon::parse('6 days ago')]);
        $order6 = \OrderFactory::createForConcert($concert, 1, ['created_at' => Carbon::parse('5 days ago')]);
        $order7 = \OrderFactory::createForConcert($concert, 1, ['created_at' => Carbon::parse('4 days ago')]);
        $order8 = \OrderFactory::createForConcert($concert,1,  ['created_at' => Carbon::parse('3 days ago')]);
        $order9 = \OrderFactory::createForConcert($concert, 1, ['created_at' => Carbon::parse('2 days ago')]);
        $order10 = \OrderFactory::createForConcert($concert, 1, ['created_at' => Carbon::parse('1 days ago')]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->data('orders')->assertEquals([
            $order10,
            $order9,
            $order8,
            $order7,
            $order6,
            $order5,
            $order4,
            $order3,
            $order2,
            $order1
        ]);

        $response->data('orders')->assertNotContains($oldOrder);
    }

    /** @test */
    function a_promoter_cannot_view_the_orders_of_unpublished_concerts()
    {
        $user = factory(User::class)->create();
        $concert = \ConcertFactory::createUnpublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /** @test */
    function a_promoter_cannot_view_the_orders_of_another_published_concert()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = \ConcertFactory::createPublished(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /** @test */
    function a_guest_cannot_view_the_orders_of_any_published_concert()
    {
        $concert = \ConcertFactory::createPublished();

        $response = $this->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertRedirect('/login');
    }
}
