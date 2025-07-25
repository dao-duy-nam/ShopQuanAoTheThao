<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactReplyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $contact;
    public $replyContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Contact $contact, $replyContent)
    {
        $this->contact = $contact;
        $this->replyContent = $replyContent;
    }

   
    public function build()
    {
        return $this->subject('Phản hồi liên hệ của bạn')
                    ->view('emails.contact_reply');
    }
}
