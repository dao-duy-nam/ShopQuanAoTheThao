<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $messageContent;

    public function __construct(Order $order, $messageContent)
    {
        $this->order = $order;
        $this->messageContent = $messageContent;
    }

    public function build()
    {
        return $this->subject('Cập nhật trạng thái đơn hàng')
                    ->view('emails.order-status-changed');
    }
}
