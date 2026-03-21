<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BroadcastMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $bodyHtml;

    public function __construct(string $subject, string $bodyHtml)
    {
        $this->subject($subject);
        $this->bodyHtml = $bodyHtml;
    }

    public function build()
    {
        return $this->view('emails.broadcast')
            ->with(['body' => $this->bodyHtml])
            ->subject($this->subject ?? 'Announcement');
    }
}
