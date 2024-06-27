<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct()
    {
        // Any dependencies or data passed to the mail class
    }

    public function build()
    {
        return $this->view('emails.index');
    }
}

