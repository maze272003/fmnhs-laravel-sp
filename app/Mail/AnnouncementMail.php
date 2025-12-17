<?php

namespace App\Mail;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    public $announcement;

    // Tanggapin ang announcement data galing sa Controller
    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Announcement: ' . $this->announcement->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.announcement', // Ito ang gagawin nating blade file mamaya
        );
    }

    public function attachments(): array
    {
        return [];
    }
}