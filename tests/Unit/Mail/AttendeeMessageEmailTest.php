<?php

namespace Tests\Unit\Mail;

use App\AttendeeMessage;
use App\Mail\AttendeeMessageMail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendeeMessageEmailTest extends TestCase
{

    /** @test */

    function email_has_correct_subject_and_message_text()
    {
        $message = new AttendeeMessage([
            'subject' => 'Message test subject',
            'message' => 'Test message'
        ]);

        $email = new AttendeeMessageMail($message);

        $this->assertEquals('Message test subject', $email->build()->subject);
        $this->assertEquals('Test message', trim($this->render($email)));
    }

    private function render($mailable) {
        $mailable->build();

        return view($mailable->textView, $mailable->buildViewdata())->render();
    }
}
