<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderCancelledMail;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';
    protected $description = 'Huỷ đơn hàng đã quá hạn thanh toán và gửi email.';

    public function handle()
    {
        $orders = Order::where('trang_thai_thanh_toan', 'cho_xu_ly')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($orders as $order) {
            $order->update([
                'trang_thai_thanh_toan' => 'that_bai',
                'trang_thai_don_hang'   => 'da_huy',
                'payment_link'          => null,
            ]);

            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)->queue(
                    new OrderCancelledMail($order, 'Đơn hàng đã bị huỷ vì quá hạn thanh toán.')
                );
            }

            $this->info("Huỷ đơn hàng ID: {$order->id}");
        }

        return 0;
    }
}
