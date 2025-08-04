<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CartItemIssueMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;


    public $messages;

    public function __construct($messages)
    {
        $this->messages = $messages;
    }

    public function build()
    {
        return $this->subject('Thông báo về sản phẩm trong giỏ hàng')
                    ->view('emails.cart_issue');
    }
}
