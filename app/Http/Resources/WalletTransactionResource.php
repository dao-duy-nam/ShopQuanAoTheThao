<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
{
    return [
        'id' => $this->id,
        'user_id' => $this->user_id,
        'wallet_id' => $this->wallet_id,
        'transaction_code' => $this->transaction_code,
        'type' => $this->type,
        'amount' => $this->amount,
        'status' => $this->status,
        'description' => $this->description,
        'related_order_id' => $this->related_order_id,
        'bank_name' => $this->bank_name,
        'bank_account' => $this->bank_account,
        'acc_name' => $this->acc_name,
        'transfer_image' => $this->transfer_image ? asset('storage/' . $this->transfer_image) : null,
        'rejection_reason' => $this->rejection_reason,
        'expires_at' => $this->expires_at,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
    ];
}

} 