<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderCancelledMail;

class CancelExpiredZaloPayOrders extends Command
{
    protected $signature = 'orders:cancel-expired-zalopay';
    protected $description = 'Tự động hủy đơn ZaloPay quá 15 phút chưa thanh toán';

    public function handle()
    {
        $now = now();

        try {
            $orders = Order::where('phuong_thuc_thanh_toan_id', 3)
                ->whereIn('trang_thai_thanh_toan', ['chua_thanh_toan', 'cho_xu_ly'])
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', $now)
                ->get();

            if ($orders->isEmpty()) {
                $this->info('❎ Không có đơn hàng quá hạn cần hủy.');
                return;
            }

            foreach ($orders as $order) {
                $order->update([
                    'trang_thai_don_hang' => 'da_huy',
                    'trang_thai_thanh_toan' => 'that_bai',
                    'payment_link' => null,
                ]);

                if ($order->user && $order->user->email) {
                    Mail::to($order->user->email)->queue(new OrderCancelledMail($order));
                }

                $this->info("✅ Đã hủy đơn hàng #{$order->id}");
                Log::info("Đã hủy đơn hàng quá hạn (ZaloPay): {$order->id}");
            }

            $this->info('🎯 Đã xử lý xong tất cả đơn quá hạn.');
        } catch (\Exception $e) {
            Log::error('❌ Lỗi khi hủy đơn quá hạn ZaloPay: ' . $e->getMessage());
            $this->error('Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }
}
