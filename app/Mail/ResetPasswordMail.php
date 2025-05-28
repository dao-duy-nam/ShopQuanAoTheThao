<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;

class ResetPasswordMail extends Mailable
{
    public $resetLink;

    public function __construct($resetLink)
    {
        $this->resetLink = $resetLink;
    }

    public function build()
    {
        return $this->subject('Đặt lại mật khẩu')
                    ->view('emails.reset_password')
                    ->with(['resetLink' => $this->resetLink]);
    }
}
