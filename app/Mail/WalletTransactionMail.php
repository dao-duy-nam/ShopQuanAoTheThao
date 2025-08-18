<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\WalletTransaction;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WalletTransactionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $transaction;
    public $subjectText;

    public function __construct(WalletTransaction $transaction, string $subjectText)
    {
        $this->transaction = $transaction->load(['wallet', 'user']);
        $this->transaction = $transaction;
        $this->subjectText = $subjectText;
    }

    public function build()
    {
        return $this->subject($this->subjectText)
            ->view('emails.wallet_transaction');
    }
}