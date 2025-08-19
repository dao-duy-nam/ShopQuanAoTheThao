<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderStatusChangedMail extends Mailable implements ShouldQueue
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
