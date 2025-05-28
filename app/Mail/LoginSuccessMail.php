<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $loginTime;

    public function __construct($user, $loginTime)
    {
        $this->user = $user;
        $this->loginTime = $loginTime;
    }

    public function build()
    {
        return $this->markdown('emails.login_success')
                    ->subject('Thông báo đăng nhập thành công');
    }
}
