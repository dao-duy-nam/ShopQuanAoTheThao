<?php

namespace App\Http\Controllers\Api\Payment;

use Carbon\Carbon;
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
use Illuminate\Support\Facades\Mail;

class VnpayController extends Controller
{
    public function createPayment(Request $request)
    {
        $data = $request->validate([
            'don_hang_id'               => 'required|exists:don_hangs,id',
            'bank_code'                 => 'nullable|string|max:20',
            'ngon_ngu'                  => 'nullable|in:vn,en',
            'phuong_thuc_thanh_toan_id' => 'required|exists:phuong_thuc_thanh_toans,id',
        ]);

        $order = Order::where('id', $data['don_hang_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Đơn hàng không tồn tại '], 404);
        }

        if ($order->trang_thai_thanh_toan !== 'cho_xu_ly') {
            return response()->json(['message' => 'Đơn hàng không hợp lệ để thanh toán'], 409);
        }

        if ($order->phuong_thuc_thanh_toan_id != 2) {
            return response()->json(['message' => 'Phương thức không hợp lệ để dùng VNPay'], 400);
        }

        $vnp_TmnCode    = config('services.vnpay.tmn_code');
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_Url        = config('services.vnpay.url');
        $vnp_ReturnUrl  = config('services.vnpay.return_url');

        $vnp_TxnRef = 'DH_' . mt_rand(1000000000, 9999999999);

        if (
            $order->payment_link &&
            $order->ma_don_hang === $vnp_TxnRef &&
            $order->expires_at &&
            Carbon::parse($order->expires_at)->isFuture()
        ) {
            return response()->json([
                'pay_url'     => $order->payment_link,
                'don_hang_id' => $order->id,
                'expires_at'  => $order->expires_at,
            ]);
        }

        $expiresAt = now()->addMinutes(15);

        $inputData = [
            'vnp_Version'    => '2.1.0',
            'vnp_TmnCode'    => $vnp_TmnCode,
            'vnp_Amount'     => $order->so_tien_thanh_toan * 100,
            'vnp_Command'    => 'pay',
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_CurrCode'   => 'VND',
            'vnp_IpAddr'     => $request->ip(),
            'vnp_Locale'     => $data['ngon_ngu'] ?? 'vn',
            'vnp_OrderInfo'  => 'Thanh toán đơn hàng #' . $vnp_TxnRef,
            'vnp_OrderType'  => 'billpayment',
            'vnp_ReturnUrl'  => $vnp_ReturnUrl,
            'vnp_TxnRef'     => $vnp_TxnRef,
            'vnp_ExpireDate' => $expiresAt->format('YmdHis'),
        ];

        if (!empty($data['bank_code'])) {
            $inputData['vnp_BankCode'] = $data['bank_code'];
        }

        ksort($inputData);
        $hashData = '';
        foreach ($inputData as $key => $value) {
            $hashData .= ($hashData ? '&' : '') . $key . '=' . urlencode($value);
        }

        $vnp_SecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $query = http_build_query($inputData, '', '&', PHP_QUERY_RFC3986);
        $paymentUrl = $vnp_Url . '?' . $query . '&vnp_SecureHash=' . $vnp_SecureHash;

        $order->update([
            'ma_don_hang'  => $vnp_TxnRef,
            'expires_at'   => $expiresAt,
            'payment_link' => $paymentUrl,
        ]);

        Log::info('VNPay Redirect URL:', [
            'url'         => $paymentUrl,
            'data'        => $inputData,
            'hash_data'   => $query,
            'secure_hash' => $vnp_SecureHash,
        ]);

        return response()->json([
            'pay_url'     => $paymentUrl,
            'don_hang_id' => $order->id,
            'expires_at'  => $expiresAt,
        ], 201);
    }


    public function callback(Request $request)
    {
        return $this->handleVnpResponse($request);
    }

    public function ipn(Request $request)
    {
        return $this->handleVnpResponse($request, true);
    }

    private function handleVnpResponse(Request $request, bool $isIpn = false)
    {
        $hashSecret = config('services.vnpay.hash_secret');
        $input = $request->all();

        Log::info('[VNPAY] Dữ liệu nhận từ VNPay:', $input);

        $secureHash = $input['vnp_SecureHash'] ?? '';
        unset($input['vnp_SecureHash'], $input['vnp_SecureHashType']);

        ksort($input);
        $hashData = '';
        foreach ($input as $key => $value) {
            $hashData .= ($hashData ? '&' : '') . $key . '=' . urlencode($value);
        }
        $calcHash = hash_hmac('sha512', $hashData, $hashSecret);

        Log::info('[VNPAY] So sánh chữ ký:', [
            'query' => $hashData,
            'secure_hash_from_vnp' => $secureHash,
            'secure_hash_calculated' => $calcHash,
            'match' => $secureHash === $calcHash,
        ]);

        if ($calcHash !== $secureHash) {
            Log::warning('VNPay signature mismatch', ['query' => $input]);
            return $this->vnpResponse($isIpn, '97', 'Chữ ký không hợp lệ');
        }

        $order = Order::with('orderDetail.variant')
            ->where('ma_don_hang', $input['vnp_TxnRef'] ?? '')
            ->lockForUpdate()
            ->first();

        if (!$order) {
            return $this->vnpResponse($isIpn, '01', 'Đơn hàng không tồn tại');
        }


        if ($order->expires_at && $order->expires_at < now() && $order->trang_thai_thanh_toan === 'cho_xu_ly') {
            $order->update([
                'trang_thai_thanh_toan' => 'that_bai',
                'trang_thai_don_hang'   => 'da_huy',
                'payment_link'          => null,
            ]);

            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)->queue(
                    new OrderCancelledMail($order, 'Đơn hàng của bạn đã bị huỷ do quá thời hạn thanh toán.')
                );
            }

            return $this->vnpResponse($isIpn, '02', 'Đơn hàng đã hết hạn');
        }

        if ($order->trang_thai_thanh_toan === 'da_thanh_toan') {
            return $this->vnpResponse($isIpn, '00', 'Thanh toán thành công', $order);
        }

        $respCode = $input['vnp_ResponseCode'] ?? null;

        DB::transaction(function () use ($order, $respCode) {
            if ($respCode === '00') {
                $variantsCache = [];
                $outOfStockItems = [];

                foreach ($order->orderDetail as $detail) {
                    if ($detail->bien_the_id) {
                        $bienThe = Variant::lockForUpdate()->find($detail->bien_the_id);
                        $variantsCache[$detail->bien_the_id] = $bienThe;

                        if (!$bienThe || $bienThe->so_luong < $detail->so_luong) {
                            $outOfStockItems[] = 'Biến thể #' . $detail->bien_the_id;
                        }
                    }
                }

                if (empty($outOfStockItems)) {
                    foreach ($order->orderDetail as $detail) {
                        if ($detail->bien_the_id && isset($variantsCache[$detail->bien_the_id])) {
                            $bienThe = $variantsCache[$detail->bien_the_id];
                            $bienThe->decrement('so_luong', $detail->so_luong);
                            $bienThe->increment('so_luong_da_ban', $detail->so_luong);
                        }
                    }

                    $productIds = $order->orderDetail->pluck('variant.san_pham_id')->unique();
                    foreach ($productIds as $productId) {
                        $variants = Variant::where('san_pham_id', $productId)->get();
                        $tongSoLuong = $variants->sum('so_luong');
                        $tongDaBan = $variants->sum('so_luong_da_ban');

                        Product::where('id', $productId)->update([
                            'so_luong'        => $tongSoLuong,
                            'so_luong_da_ban' => $tongDaBan,
                        ]);
                    }

                    $order->update([
                        'trang_thai_thanh_toan' => 'da_thanh_toan',
                        'trang_thai_don_hang'   => 'cho_xac_nhan',
                        'payment_link'          => null,
                        'expires_at'            => null,
                        'ngay_thanh_toan'       => now(),
                    ]);
                    $order->refresh();

                    if ($order->user && $order->user->email) {
                        Mail::to($order->user->email)->queue(new OrderPaidMail($order));
                    }
                } else {
                    $ghiChuAdmin = 'Thiếu tồn kho: ' . implode(', ', $outOfStockItems);

                    $order->update([
                        'trang_thai_thanh_toan' => 'da_thanh_toan',
                        'trang_thai_don_hang'   => 'cho_xac_nhan',
                        'ghi_chu_admin'         => $ghiChuAdmin,
                        'payment_link'          => null,
                        'expires_at'            => null,
                        'ngay_thanh_toan'       => now(),
                    ]);
                    $order->refresh();

                    if ($order->user && $order->user->email) {
                        Mail::to($order->user->email)->queue(
                            new OrderOutOfStockMail($order)
                        );
                    }
                }
            } elseif ($respCode === '24') {

                $order->update([
                    'trang_thai_thanh_toan' => 'that_bai',
                    'trang_thai_don_hang'   => 'da_huy',
                    'payment_link'          => null,
                    'expires_at'            => null,
                ]);
                $order->refresh();
                if ($order->user && $order->user->email) {
                    Mail::to($order->user->email)->queue(
                        new OrderCancelledMail($order, 'Khách hàng đã hủy giao dịch VNPay.')
                    );
                }
            }
        });

        if ($respCode !== '00') {
            Log::warning('VNPay thất bại', [
                'don_hang_id' => $order->id,
                'resp'        => $respCode
            ]);
        }

        $order->load('orderDetail.variant');

        return $this->vnpResponse(
            $isIpn,
            $respCode,
            $order
        );
    }

    private function vnpResponse(bool $isIpn, ?string $code, $order = null)
    {
        $messages = [
            '00' => 'Giao dịch thành công',
            '01' => 'Không tìm thấy đơn hàng',
            '11' => 'Đơn hàng đã hết hạn',
            '24' => 'Khách hàng đã hủy giao dịch',
            '51' => 'Số dư không đủ',
            '65' => 'Vượt hạn mức giao dịch',
            '75' => 'Ngân hàng đang bảo trì',
            '97' => 'Sai chữ ký (checksum)',
            '99' => 'Lỗi hệ thống',
        ];

        $message = $messages[$code] ?? 'Thanh toán thất bại';

        if ($isIpn) {
            return response()->json([
                'RspCode' => $code,
                'Message' => $message
            ]);
        }

        return response()->json([
            'code'    => $code,
            'message' => $message,
            'order'   => $order
        ]);
    }
}
