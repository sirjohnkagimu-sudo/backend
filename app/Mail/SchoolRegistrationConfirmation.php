<?php

namespace App\Mail;

use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SchoolRegistrationConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public School $school;

    public function __construct(School $school)
    {
        $this->school = $school;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'School Account Created - ' . $this->school->name,
            from: 'noreply@edumallug.com',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.school_registration_confirmation',
            with: [
                'school' => $this->school,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
