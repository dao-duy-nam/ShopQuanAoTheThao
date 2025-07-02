<?php
namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderPaidMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build(): static
    {
        return $this->subject('Xác nhận thanh toán thành công đơn hàng')
                    ->view('emails.order_paid')
                    ->with([
                        'order' => $this->order,
                    ]);
    }
}
