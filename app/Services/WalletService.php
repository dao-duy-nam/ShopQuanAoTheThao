<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Mail\WalletTransactionMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WalletService
{
   public function refund($user, $orderId, $amount)
    {
        $order = Order::findOrFail($orderId);

        if ($order->user_id !== $user->id) {
            throw new \Exception('Bạn không có quyền hoàn tiền cho đơn hàng này.');
        }

        if ($order->refund_done) {
            throw new \Exception('Đơn hàng này đã được hoàn tiền.');
        }

        DB::beginTransaction();

        try {
            $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

            // Tạo giao dịch hoàn tiền
            $transaction = WalletTransaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'transaction_code' => 'REFUND_' . strtoupper(\Illuminate\Support\Str::random(6)),
                'type' => 'refund',
                'amount' => $amount,
                'status' => 'success',
                'description' => 'Hoàn tiền cho đơn hàng ' . $order->ma_don_hang,
                'related_order_id' => $order->id,
            ]);

            
            $wallet->increment('balance', $amount);
            $order->update(['refund_done' => true]);
            Mail::to($user->email)->queue(new WalletTransactionMail($transaction, 'Hoàn tiền thành công cho đơn hàng ' . $order->ma_don_hang));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
} 