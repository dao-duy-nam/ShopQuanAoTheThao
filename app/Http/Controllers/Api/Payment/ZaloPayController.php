<?php

namespace App\Http\Controllers\Api\Payment;

use App\Models\Order;
use App\Models\Product;
use App\Models\Variant;
use App\Mail\OrderPaidMail;
use Illuminate\Http\Request;
use App\Mail\OrderCancelledMail;
use App\Mail\OrderOutOfStockMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class ZaloPayController extends Controller
{
    public function createPayment(Request $request)
    {
        $data = $request->validate([
            'don_hang_id' => 'required|exists:don_hangs,id',
            'phuong_thuc_thanh_toan_id' => 'required|exists:phuong_thuc_thanh_toans,id',
        ]);

        $order = Order::with('orderDetail')
            ->where('id', $data['don_hang_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($order->trang_thai_thanh_toan !== 'cho_xu_ly') {
            return response()->json(['message' => 'Đơn hàng không hợp lệ để thanh toán'], 409);
        }

        if ($data['phuong_thuc_thanh_toan_id'] != 3) {
            return response()->json(['message' => 'Phương thức không phù hợp để thanh toán bằng ZaloPay'], 400);
        }


        if (
            $order->payment_link &&
            $order->expires_at &&
            \Carbon\Carbon::parse($order->expires_at)->isFuture()
        ) {
            return response()->json([
                'pay_url' => $order->payment_link,
                'don_hang_id' => $order->id,
                'expires_at' => $order->expires_at,
            ]);
        }

        $app_id = config('services.zalopay.app_id');
        $key1 = config('services.zalopay.key1');
        $callback = config('services.zalopay.callback_url');
        $redirect = config('services.zalopay.redirect_url');

        $today = date("ymd");
        $app_trans_id = $today . "_" . uniqid($order->id . "_");
        $app_user = (string) $order->user_id;
        $amount = (int) $order->so_tien_thanh_toan;
        $app_time = round(microtime(true) * 1000);
        $expiresAt = now()->addMinutes(15);

        $items = [];
        foreach ($order->orderDetail as $detail) {
            $items[] = [
                'itemid' => $detail->id,
                'itemname' => $detail->ten_san_pham ?? 'Sản phẩm',
                'itemprice' => (int) $detail->don_gia,
                'itemquantity' => (int) $detail->so_luong,
            ];
        }

        if (count($items) === 0) {
            return response()->json(['message' => 'Đơn hàng không có sản phẩm để thanh toán'], 422);
        }

        $itemJson = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $embed_data = json_encode([
            'redirecturl' => $redirect . '?ma_don_hang=' . $app_trans_id,
            'callback_url' => $callback
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


        $mac_string = implode("|", [
            $app_id,
            $app_trans_id,
            $app_user,
            $amount,
            $app_time,
            $embed_data,
            $itemJson
        ]);

        $mac = hash_hmac("sha256", $mac_string, $key1);

        $zalopayData = [
            "app_id" => $app_id,
            "app_trans_id" => $app_trans_id,
            "app_user" => $app_user,
            "app_time" => $app_time,
            "amount" => $amount,
            "description" => "Thanh toán đơn hàng #" . $app_trans_id,
            "bank_code" => "",
            "item" => $itemJson,
            "embed_data" => $embed_data,
            "mac" => $mac,
        ];

        $response = Http::asForm()->post("https://sb-openapi.zalopay.vn/v2/create", $zalopayData);
        $result = $response->json();

        if ($response->status() !== 200 || !isset($result['order_url'])) {
            return response()->json([
                'message' => 'Không tạo được order_url từ ZaloPay',
                'zalopay_response' => $result
            ], 500);
        }


        $order->update([
            'ma_don_hang' => $app_trans_id,
            'payment_link' => $result['order_url'],
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'pay_url' => $result['order_url'],
            'don_hang_id' => $order->id,
            'expires_at' => $expiresAt,
        ]);
    }

    public function callback(Request $request)
    {
        return $this->handleZaloResponse($request, false);
    }


    private function handleZaloResponse(Request $request, bool $isIpn = false)
    {
        Log::info('[ZaloPay] Callback nhận về:', $request->all());

        $data = $request->all();
        if (!isset($data['app_trans_id']) || !isset($data['status'])) {
            return $this->zaloResponse($isIpn, '01', 'Thiếu dữ liệu callback');
        }

        $parts = explode('_', $data['app_trans_id']);
        $orderId = $parts[1] ?? null;

        $order = Order::with('orderDetail.variant')
            ->where('id', $orderId)
            ->where('ma_don_hang', $data['app_trans_id'])
            ->lockForUpdate()
            ->first();

        if (!$order) {
            return $this->zaloResponse($isIpn, '01', 'Không tìm thấy đơn hàng');
        }
        if ((int)$data['status'] === 1) {
            try {
                DB::beginTransaction();

                $outOfStock = [];
                $variantCache = [];

                foreach ($order->orderDetail as $detail) {
                    $variant = Variant::lockForUpdate()->find($detail->bien_the_id);
                    if (!$variant || $variant->so_luong < $detail->so_luong) {
                        $outOfStock[] = 'Biến thể #' . $detail->bien_the_id;
                    }
                    $variantCache[$detail->bien_the_id] = $variant;
                }

                if (empty($outOfStock)) {
                    foreach ($order->orderDetail as $detail) {
                        $variant = $variantCache[$detail->bien_the_id];
                        $variant->decrement('so_luong', $detail->so_luong);
                        $variant->increment('so_luong_da_ban', $detail->so_luong);
                    }

                    $productIds = $order->orderDetail->pluck('variant.san_pham_id')->unique();
                    foreach ($productIds as $productId) {
                        $variants = Variant::where('san_pham_id', $productId)->get();
                        Product::where('id', $productId)->update([
                            'so_luong'        => $variants->sum('so_luong'),
                            'so_luong_da_ban' => $variants->sum('so_luong_da_ban'),
                        ]);
                    }

                    $order->update([
                        'trang_thai_thanh_toan' => 'da_thanh_toan',
                        'trang_thai_don_hang'   => 'cho_xac_nhan',
                        'payment_link'          => null,
                        'ngay_thanh_toan'       => now(),
                    ]);

                    Mail::to($order->user->email)->queue(new OrderPaidMail($order));
                } else {
                    $order->update([
                        'trang_thai_thanh_toan' => 'da_thanh_toan',
                        'trang_thai_don_hang'   => 'cho_xac_nhan',
                        'ghi_chu_admin'         => 'Thiếu tồn kho: ' . implode(', ', $outOfStock),
                        'payment_link'          => null,
                        'ngay_thanh_toan'       => now(),
                    ]);

                    Mail::to($order->user->email)->queue(new OrderOutOfStockMail($order));
                }

                DB::commit();

                return $this->zaloResponse($isIpn, '00', 'Thanh toán thành công', $order->fresh(['user', 'orderDetail.variant']));
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('[ZaloPay] Callback lỗi xử lý', ['error' => $e->getMessage()]);
                return $this->zaloResponse($isIpn, '99', 'Lỗi xử lý callback');
            }
        }

        
        if ($order->trang_thai_don_hang !== 'da_huy') {
            $order->update([
                'trang_thai_thanh_toan' => 'that_bai',
                'trang_thai_don_hang'   => 'da_huy',
                'payment_link'          => null,

            ]);

            Mail::to($order->user->email)->queue(
                new OrderCancelledMail($order, 'Thanh toán ZaloPay không thành công. Đơn hàng đã bị huỷ.')
            );
        } else {
            Log::info('[ZaloPay] Không gửi mail huỷ vì đơn đã huỷ từ trước', [
                'order_id' => $order->id,
            ]);
        }

        return $this->zaloResponse($isIpn, '01', 'Thanh toán thất bại', $order->fresh(['user', 'orderDetail.variant']));
    }

    private function zaloResponse(bool $isIpn, string $code, string $message, $order = null)
    {
        return response()->json(
            $isIpn
                ? ['code' => $code, 'message' => $message]
                : ['code' => $code, 'message' => $message, 'order' => $order]
        );
    }
    public function redirectView(Request $request)
    {
        $maDonHang = $request->get('ma_don_hang');
        $status = $request->get('status');

        $order = Order::with('orderDetail.variant', 'user')
            ->where('ma_don_hang', $maDonHang)
            ->first();

        if (!$order) {
            return response()->json(['code' => '01', 'message' => 'Không tìm thấy đơn hàng']);
        }


        if ($order->trang_thai_thanh_toan === 'cho_xu_ly' && $status == 1) {
            $fakeRequest = new Request([
                'app_trans_id' => $maDonHang,
                'status' => 1
            ]);

            return $this->handleZaloResponse($fakeRequest, false);
        }

        return response()->json([
            'code' => $order->trang_thai_thanh_toan === 'da_thanh_toan' ? '00' : '01',
            'message' => $order->trang_thai_thanh_toan === 'da_thanh_toan' ? 'Thanh toán thành công' : 'Chưa thanh toán',
            'order' => $order
        ]);
    }
}
