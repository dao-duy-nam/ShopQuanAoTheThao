<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderCancelledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;
    public ?string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, ?string $reason = null)
    {
        $this->order = $order;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Đơn hàng #' . $this->order->ma_don_hang . ' đã bị hủy',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
{
    return new Content(
        markdown: 'emails.orders.cancelled',
        with: [
            'order' => $this->order,
            'reason' => $this->reason,
        ]
    );
}


    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
