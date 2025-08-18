<?php
namespace App\Mail;

use App\Models\User;
use App\Models\DiscountCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DiscountCodeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $code;

    public function __construct(User $user, DiscountCode $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('Quà tặng từ chúng tôi: Mã giảm giá đặc biệt cho bạn!')
                    ->view('emails.discount_code');
    }
}
