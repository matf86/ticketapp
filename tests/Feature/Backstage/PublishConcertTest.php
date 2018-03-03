<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PublishConcertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    
    function promoter_can_publish_his_concert()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $user->id,
            'ticket_quantity' => 3
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->from("/backstage/concerts")->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertRedirect('/backstage/concerts');
        $this->assertTrue($concert->fresh()->isPublished());
        $this->assertEquals(3, $concert->fresh()->ticketsRemaining());
    }

    /** @test */

    function guest_can_not_publish_a_concert()
    {
        $concert = factory(Concert::class)->states('unpublished')->create([
            'ticket_quantity' => 3
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertRedirect('/login');
        $this->assertFalse($concert->fresh()->isPublished());
        $this->assertEquals(0, $concert->fresh()->ticketsRemaining());
    }

    /** @test */

    function promoter_can_not_publish_other_users_concert()
    {
        $userA = factory(User::class)->create();
        $userB = factory(User::class)->create();

        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id' => $userB->id,
            'ticket_quantity' => 3
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->from("/backstage/concerts")->actingAs($userA)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertStatus(404);
    }

    /** @test */

    function concert_can_be_published_only_once()
    {
        $user = factory(User::class)->create();

        $concert = \ConcertFactory::createPublished([
            'user_id' => $user->id,
            'ticket_quantity' => 3
        ]);

        $this->assertTrue($concert->isPublished());

        $response = $this->from("/backstage/concerts")->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertStatus(422);
        $this->assertEquals(3, $concert->fresh()->ticketsRemaining());
    }
}
