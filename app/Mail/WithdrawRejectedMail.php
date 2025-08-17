<?php
namespace App\Mail;

use App\Models\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WithdrawRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;

    public function __construct(WalletTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function build()
    {
        return $this->subject('Thông báo rút tiền bị từ chối')
            ->view('emails.withdraw_rejected')
            ->with([
                'user'   => $this->transaction->user,
                'amount' => number_format($this->transaction->amount, 0, ',', '.'),
                'reason' => $this->transaction->rejection_reason,
                'date'   => $this->transaction->updated_at->format('d/m/Y H:i'),
            ]);
    }
}
