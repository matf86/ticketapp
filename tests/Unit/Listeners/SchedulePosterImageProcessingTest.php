<?php

namespace Tests\Unit\Listeners;

use App\Events\ConcertAdded;
use App\Jobs\ProcessPosterImage;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SchedulePosterImageProcessingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    
    function it_queues_a_job_to_process_a_poster_image_when_poster_image_is_present()
    {
        Queue::fake();

        $concert = \ConcertFactory::createUnpublished(['poster_image_path' => 'posters/example-poster.png']);

        ConcertAdded::dispatch($concert);

        Queue::assertPushed(ProcessPosterImage::class, function($job) use($concert) {
            return $job->concert->is($concert);
        });
    }

    /** @test */

    function it_does_not_queue_a_job_to_process_a_poster_image_if_there_is_no_poster_image()
    {
        Queue::fake();

        $concert = \ConcertFactory::createUnpublished(['poster_image_path' => null]);

        ConcertAdded::dispatch($concert);

        Queue::assertNotPushed(ProcessPosterImage::class);
    }
}
