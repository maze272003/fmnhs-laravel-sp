<?php

namespace App\Mail;

use App\Models\VideoConference;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConferenceDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly VideoConference $conference,
        public readonly array $summary,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Meeting Digest: {$this->conference->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.conference-digest',
            with: [
                'conference' => $this->conference,
                'summary' => $this->summary,
            ],
        );
    }
}
