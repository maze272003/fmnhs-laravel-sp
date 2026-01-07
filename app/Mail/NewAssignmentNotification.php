<?php
// App\Mail\NewAssignmentNotification.php

namespace App\Mail;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewAssignmentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $assignment;

    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
        Log::info("Mailable Initialized for Assignment ID: " . $this->assignment->id);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Class Task: ' . $this->assignment->title);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.assignment_notification');
    }

    public function attachments(): array
    {
        $attachments = [];
        if ($this->assignment->file_path) {
            $path = public_path('uploads/assignments/' . $this->assignment->file_path);
            if (file_exists($path)) {
                Log::info("Attaching file: " . $path);
                $attachments[] = Attachment::fromPath($path);
            } else {
                Log::error("Attachment file not found at: " . $path);
            }
        }
        return $attachments;
    }
}