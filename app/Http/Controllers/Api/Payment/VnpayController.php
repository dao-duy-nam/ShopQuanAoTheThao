<?php

namespace App\Http\Controllers\Api\Payment;

use App\Models\Order;
use App\Mail\OrderPaidMail;
use Illuminate\Http\Request;
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
            ->firstOrFail();


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


        if ($order->ma_don_hang && $order->expires_at && $order->expires_at->isFuture()) {
            $vnp_TxnRef = $order->ma_don_hang;
            $expiresAt  = $order->expires_at;
        } else {

            $vnp_TxnRef = strtoupper(uniqid($order->id . '_'));
            $expiresAt  = now()->addMinutes(15);

            $order->update([
                'ma_don_hang' => $vnp_TxnRef,
                'expires_at'  => $expiresAt,
            ]);
        }


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

        Log::info('VNPay Redirect URL:', [
            'url' => $paymentUrl,
            'data' => $inputData,
            'hash_data' => $query,
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
            if ($hashData != '') {
                $hashData .= '&';
            }
            $hashData .= $key . '=' . urlencode($value);
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

        $order = Order::with('orderDetail.product')
            ->where('ma_don_hang', $input['vnp_TxnRef'] ?? '')
            ->lockForUpdate()
            ->first();

        if (!$order) {
            return $this->vnpResponse($isIpn, '01', 'Đơn hàng không tồn tại');
        }

        if ((int)($input['vnp_Amount'] ?? 0) !== (int)($order->so_tien_thanh_toan * 100)) {
            return $this->vnpResponse($isIpn, '04', 'Số tiền không khớp');
        }

        if ($order->expires_at && $order->expires_at < now() && $order->trang_thai_thanh_toan === 'cho_xu_ly') {
            $order->update([
                'trang_thai_thanh_toan' => 'that_bai',
                'trang_thai_don_hang'   => 'da_huy',
            ]);
            return $this->vnpResponse($isIpn, '02', 'Đơn hàng đã hết hạn');
        }

        if ($order->trang_thai_thanh_toan === 'da_thanh_toan') {
            return $this->vnpResponse($isIpn, '00', 'Đã xử lý');
        }

        $respCode = $input['vnp_ResponseCode'] ?? null;

        DB::transaction(function () use ($order, $respCode) {
            if ($respCode === '00') {
                $order->update([
                    'trang_thai_thanh_toan' => 'da_thanh_toan',
                    'trang_thai_don_hang'   => 'dang_chuan_bi',
                    'payment_link'          => null,
                ]);

                foreach ($order->orderDetail as $detail) {
                    $detail->product()->decrement('so_luong', $detail->so_luong);
                }
                if ($order->user && $order->user->email) {
                    Mail::to($order->user->email)->queue(new OrderPaidMail($order));

                }
            } else {
                $order->update([
                    'trang_thai_thanh_toan' => 'that_bai',
                    'trang_thai_don_hang'   => 'da_huy',
                    'payment_link'          => null,
                ]);
            }
        });

        if ($respCode !== '00') {
            Log::warning('VNPay thất bại', [
                'don_hang_id' => $order->id,
                'resp' => $respCode
            ]);
        }

        return $this->vnpResponse(
            $isIpn,
            '00',
            $respCode === '00' ? 'Thanh toán thành công' : 'Thanh toán thất bại',
            $order
        );
    }


    private function vnpResponse(bool $isIpn, string $code, string $message, $order = null)
    {
        return response()->json(
            $isIpn
                ? ['RspCode' => $code, 'Message' => $message]
                : ['code' => $code, 'message' => $message, 'order' => $order]
        );
    }
}
