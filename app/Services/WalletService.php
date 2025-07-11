<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    /**
     * Tạo giao dịch nạp tiền (pending)
     */
    public function deposit($user, $amount, $payment_method)
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);
        return $wallet->transactions()->create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => $amount,
            'status' => 'pending',
            'description' => 'Deposit via ' . $payment_method,
        ]);
    }

    /**
     * Xác nhận nạp tiền thành công (callback từ cổng thanh toán)
     */
    public function confirmDeposit(WalletTransaction $transaction)
    {
        if ($transaction->status !== 'pending') return false;
        DB::transaction(function () use ($transaction) {
            $wallet = $transaction->wallet;
            $wallet->balance += $transaction->amount;
            $wallet->save();
            $transaction->status = 'success';
            $transaction->save();
        });
        return true;
    }

    /**
     * Tạo yêu cầu rút tiền (pending)
     */
    public function withdraw($user, $amount, $bank_info)
    {
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();
        if ($wallet->balance < $amount) {
            throw new \Exception('Insufficient balance');
        }
        return $wallet->transactions()->create([
            'user_id' => $user->id,
            'type' => 'withdraw',
            'amount' => $amount,
            'status' => 'pending',
            'description' => 'Withdraw to bank: ' . $bank_info,
        ]);
    }

    /**
     * Xác nhận rút tiền thành công (admin duyệt)
     */
    public function confirmWithdraw(WalletTransaction $transaction)
    {
        if ($transaction->status !== 'pending') return false;
        DB::transaction(function () use ($transaction) {
            $wallet = $transaction->wallet;
            if ($wallet->balance < $transaction->amount) {
                throw new \Exception('Insufficient balance');
            }
            $wallet->balance -= $transaction->amount;
            $wallet->save();
            $transaction->status = 'success';
            $transaction->save();
        });
        return true;
    }

    /**
     * Thanh toán đơn hàng bằng ví
     */
    public function pay($user, $order_id, $amount)
    {
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();
        if ($wallet->balance < $amount) {
            throw new \Exception('Insufficient balance');
        }
        DB::transaction(function () use ($wallet, $user, $order_id, $amount) {
            $wallet->balance -= $amount;
            $wallet->save();
            $wallet->transactions()->create([
                'user_id' => $user->id,
                'type' => 'payment',
                'amount' => $amount,
                'status' => 'success',
                'description' => 'Pay for order #' . $order_id,
                'related_order_id' => $order_id,
            ]);
        });
        return true;
    }

    /**
     * Hoàn tiền vào ví
     */
    public function refund($user, $order_id, $amount)
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);
        DB::transaction(function () use ($wallet, $user, $order_id, $amount) {
            $wallet->balance += $amount;
            $wallet->save();
            $wallet->transactions()->create([
                'user_id' => $user->id,
                'type' => 'refund',
                'amount' => $amount,
                'status' => 'success',
                'description' => 'Refund for order #' . $order_id,
                'related_order_id' => $order_id,
            ]);
        });
        return true;
    }
} 