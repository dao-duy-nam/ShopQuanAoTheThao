<?php

namespace App\Mail;

use App\Models\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WithdrawSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;

    /**
     * Create a new message instance.
     */
    public function __construct(WalletTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Thông báo rút tiền thành công')
            ->view('emails.withdraw_success')
            ->with([
                'user'   => $this->transaction->user,
                'amount' => number_format($this->transaction->amount, 0, ',', '.'),
                'date'   => $this->transaction->updated_at->format('d/m/Y H:i'),
            ]);
    }
}   
