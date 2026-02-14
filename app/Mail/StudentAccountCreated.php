<?php

namespace App\Mail;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $student;

    public function __construct(Student $student, $password = null)
    {
        $this->student = $student;
    }

    public function build()
    {
        return $this->subject('Welcome to the Student Portal - Account Details')
                    ->view('emails.student_account');
    }
}