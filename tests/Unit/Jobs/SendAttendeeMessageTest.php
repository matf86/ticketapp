<?php

namespace Tests\Unit\Jobs;

use App\AttendeeMessage;
use App\Concert;
use App\Jobs\SendAttendeeMessage;
use App\Mail\AttendeeMessageMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendAttendeeMessageTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    
    function it_sends_message_to_all_concert_attendees()
    {
        Mail::fake();

        $concert = \ConcertFactory::createPublished();
        $otherConcert = \ConcertFactory::createPublished();

        $message = AttendeeMessage::create([
            'concert_id' => $concert->id,
            'subject' => 'Message test subject',
            'message' => 'Test message'
        ]);

        $orderA = \OrderFactory::createForConcert($concert, 2, ['email' => 'exampleA@test.com']);
        $orderB = \OrderFactory::createForConcert($concert, 3, ['email' => 'exampleB@test.com']);
        $otherOrder = \OrderFactory::createForConcert($otherConcert, 3, ['email' => 'otherConcertEmail@example.com']);
        $orderC = \OrderFactory::createForConcert($concert, 1, ['email' => 'exampleC@test.com']);

        SendAttendeeMessage::dispatch($message);

        Mail::assertQueued(AttendeeMessageMail::class, function($mail) use ($message) {
                return $mail->hasTo('exampleA@test.com') &&
                        $mail->message->is($message);
        });

        Mail::assertQueued(AttendeeMessageMail::class, function($mail) use ($message) {
            return $mail->hasTo('exampleB@test.com') &&
                $mail->message->is($message);
        });

        Mail::assertQueued(AttendeeMessageMail::class, function($mail) use ($message) {
            return $mail->hasTo('exampleC@test.com') &&
                $mail->message->is($message);
        });

        Mail::assertNotQueued(AttendeeMessageMail::class, function($mail) {
            return $mail->hasTo('otherConcertEmail@example.com');
        });
    }
}
