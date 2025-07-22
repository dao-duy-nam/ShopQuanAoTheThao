<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderOutOfStockMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thiếu hàng trong đơn hàng #' . $this->order->ma_don_hang,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.out_of_stock'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
