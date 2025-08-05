<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'transaction_code',
        'type',
        'amount',
        'status',
        'bank_name',
        'acc_name',
        'bank_account',
        'description',
        'related_order_id',
        'rejection_reason',
        'expires_at',
        'payment_url'
    ];
    protected $casts = [
        'expires_at' => 'datetime', 
    ];
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
     public function order()
    {
        return $this->belongsTo(Order::class, 'related_order_id');
    }
}
