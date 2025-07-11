<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPaidMail;
use App\Mail\OrderCancelledMail;
use App\Models\Order;
use App\Models\Variant;
use App\Models\Product;

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

        $app_id = config('services.zalopay.app_id');
        $key1 = config('services.zalopay.key1');
        $callback = config('services.zalopay.callback_url');
        $redirect = config('services.zalopay.redirect_url');

        $today = date("ymd");
        $app_trans_id = $today . "_" . uniqid($order->id . "_");
        $app_user = (string) $order->user_id;
        $amount = (int) $order->so_tien_thanh_toan;
        $app_time = round(microtime(true) * 1000);

        $order->update(['ma_don_hang' => $app_trans_id]);

        $item = [];
        foreach ($order->orderDetail as $detail) {
            $item[] = [
                'itemid' => $detail->id,
                'itemname' => $detail->ten_san_pham ?? 'Sản phẩm',
                'itemprice' => (int) $detail->don_gia,
                'itemquantity' => (int) $detail->so_luong,
            ];
        }

        if (count($item) === 0) {
            return response()->json(['message' => 'Đơn hàng không có sản phẩm để thanh toán'], 422);
        }

        $itemJson = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $embed_data = json_encode(new \stdClass(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

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

        if (!isset($result['order_url'])) {
            return response()->json([
                'message' => 'Không tạo được order_url từ ZaloPay',
                'zalopay_response' => $result
            ], 500);
        }

        $order->update(['payment_link' => $result['order_url']]);

        return response()->json([
            'pay_url' => $result['order_url'],
            'don_hang_id' => $order->id,
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    public function callback(Request $request)
    {
        Log::info('ZaloPay callback nhận về', $request->all());

        $data = $request->all();

        if (!isset($data['app_trans_id']) || !isset($data['status'])) {
            return response()->json(['message' => 'Thiếu dữ liệu callback'], 400);
        }

        $order = Order::with('orderDetail.variant')->where('ma_don_hang', $data['app_trans_id'])->first();

        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        if ((int)$data['status'] === 1) {
            DB::beginTransaction();
            try {
                $outOfStock = [];

                foreach ($order->orderDetail as $detail) {
                    $variant = Variant::find($detail->variant_id);
                    if (!$variant || $variant->so_luong < $detail->so_luong) {
                        $outOfStock[] = 'Biến thể #' . $detail->variant_id;
                    }
                }

                if (empty($outOfStock)) {
                    foreach ($order->orderDetail as $detail) {
                        Variant::where('id', $detail->variant_id)
                            ->decrement('so_luong', $detail->so_luong);
                        Variant::where('id', $detail->variant_id)
                            ->increment('so_luong_da_ban', $detail->so_luong);
                    }

                    $productIds = $order->orderDetail->pluck('variant.san_pham_id')->unique();
                    foreach ($productIds as $productId) {
                        $variants = Variant::where('san_pham_id', $productId)->get();
                        Product::where('id', $productId)->update([
                            'so_luong' => $variants->sum('so_luong'),
                            'so_luong_da_ban' => $variants->sum('so_luong_da_ban'),
                        ]);
                    }

                    $order->update([
                        'trang_thai_thanh_toan' => 'da_thanh_toan',
                        'trang_thai_don_hang'   => 'dang_chuan_bi',
                        'ngay_thanh_toan'       => now(),
                        'payment_link'          => null,
                    ]);

                    if ($order->user && $order->user->email) {
                        Mail::to($order->user->email)->queue(new OrderPaidMail($order));
                    }
                } else {
                    $order->update([
                        'trang_thai_thanh_toan' => 'da_thanh_toan',
                        'trang_thai_don_hang'   => 'cho_xac_nhan',
                        'ghi_chu_admin'         => 'Thiếu tồn kho: ' . implode(', ', $outOfStock),
                        'ngay_thanh_toan'       => now(),
                        'payment_link'          => null,
                    ]);

                    if ($order->user && $order->user->email) {
                        Mail::to($order->user->email)->queue(
                            new OrderCancelledMail($order, 'Chúng tôi phát hiện một số sản phẩm hết hàng. Vui lòng chờ admin xác nhận lại.')
                        );
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('ZaloPay callback lỗi', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'Lỗi xử lý callback'], 500);
            }

            return response()->json(['message' => 'Đã xử lý đơn hàng thành công']);
        } else {
            $order->update([
                'trang_thai_thanh_toan' => 'that_bai',
                'trang_thai_don_hang'   => 'da_huy',
            ]);

            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)->queue(new OrderCancelledMail($order));
            }

            return response()->json(['message' => 'Đơn hàng đã bị huỷ hoặc không thành công']);
        }
    }
}