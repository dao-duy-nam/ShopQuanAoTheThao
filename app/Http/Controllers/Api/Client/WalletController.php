<?php

namespace App\Http\Controllers\Api\Client;

use App\Models\Wallet;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\WalletService;
use App\Http\Requests\PayRequest;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Mail\WalletTransactionMail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Mail;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\WithdrawRequest;
use App\Http\Resources\WalletResource;
use App\Http\Resources\WalletTransactionResource;


class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function checkPendingTransaction(Request $request)
    {
        $user = $request->user();

        $transaction = WalletTransaction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($transaction) {
            return response()->json([
                'status' => 'pending',
                'message' => 'Bạn có giao dịch nạp tiền đang chờ',
                'data' => [
                    'transaction_code' => $transaction->transaction_code,
                    'amount' => $transaction->amount,
                    'expires_at' => $transaction->expires_at,
                    'payment_url' => $transaction->payment_url,
                ]
            ]);
        }

        return response()->json([
            'status' => 'no_pending',
            'message' => 'Không có giao dịch nạp tiền đang chờ xử lý'
        ]);
    }

    // Lấy số dư ví
    public function getBalance(Request $request)
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $request->user()->id]);
        return new WalletResource($wallet);
    }



    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50000|max:500000000',
            'bank_name' => 'required|string|max:255',
            'bank_account' => 'required|string|max:50',
            'acc_name' => 'required|string|max:255',
        ], [
            'amount.required' => 'Vui lòng nhập số tiền cần rút.',
            'amount.numeric' => 'Số tiền phải là một số.',
            'amount.min' => 'Số tiền rút tối thiểu là 50.000 VNĐ.',
            'amount.max' => 'Số tiền rút tối đa là 500.000.000 VNĐ.',
            'bank_name.required' => 'Vui lòng nhập tên ngân hàng.',
            'bank_account.required' => 'Vui lòng nhập số tài khoản.',
            'acc_name.required' => 'Vui lòng nhập tên chủ tài khoản.',
        ]);

        $user = $request->user();
        $wallet = $user->wallet;

        if (!$wallet || $wallet->balance < $request->amount) {
            return response()->json(['message' => 'Số dư không đủ để rút tiền'], 400);
        }

        try {
            $transactionCode = 'RUT_' . time();
            $wallet->decrement('balance', $request->amount);

            $transaction = $wallet->transactions()->create([
                'user_id' => $user->id,
                'transaction_code' => $transactionCode,
                'type' => 'withdraw',
                'amount' => $request->amount,
                'status' => 'pending',
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'acc_name' => $request->acc_name,
                'description' => 'Yêu cầu rút tiền đang chờ admin duyệt',
            ]);

            return new WalletTransactionResource($transaction);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi rút tiền: ' . $e->getMessage()], 500);
        }
    }





    public function refund(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'amount' => 'required|numeric|min:1000',
        ]);
        $this->walletService->refund($request->user(), $request->order_id, $request->amount);
        return response()->json(['message' => 'Refund successful']);
    }
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000|max:500000000',
        ], [
            'amount.required' => 'Vui lòng nhập số tiền cần nạp.',
            'amount.numeric' => 'Số tiền phải là một số.',
            'amount.min' => 'Số tiền nạp tối thiểu là 10.000 VNĐ.',
            'amount.max' => 'Số tiền nạp tối đa là 500.000.000 VNĐ.',
        ]);


        $user = $request->user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);
        $amount = $request->amount;
        $code = 'NAP_' . time();


        $transaction = WalletTransaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'transaction_code' => $code,
            'amount' => $amount,
            'type' => 'deposit',
            'status' => 'pending',
            'description' => 'Nạp tiền vào ví',
            'expires_at' => now()->addMinutes(15),

        ]);


        $vnp_Url = config('services.vnpay.url');
        $vnp_ReturnUrl = config('services.vnpay.wallet_return_url');
        $vnp_TmnCode = config('services.vnpay.tmn_code');
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_Amount = $amount * 100;


        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => now()->format('YmdHis'),
            "vnp_ExpireDate" => now()->addMinutes(15)->format('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $request->ip(),
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => "Nạp tiền vào ví",
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $code,
            "vnp_BankCode" => "NCB",
        ];


        ksort($inputData);
        $hashData = '';
        foreach ($inputData as $key => $value) {
            $hashData .= ($hashData ? '&' : '') . $key . '=' . urlencode($value);
        }

        $vnp_SecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $query = http_build_query($inputData, '', '&', PHP_QUERY_RFC3986);
        $redirectUrl = $vnp_Url . '?' . $query . '&vnp_SecureHash=' . $vnp_SecureHash;
        $transaction->update(['payment_url' => $redirectUrl]);
        return response()->json([
            'status' => 'success',
            'message' => 'Tạo giao dịch nạp tiền thành công',
            'data' => [
                'transaction_code' => $code,
                'amount' => $amount,
                'status' => 'pending',
                'payment_url' => $redirectUrl,
            ]
        ]);
    }


    public function vnpayWalletCallback(Request $request)
    {
        return $this->handleVnpWalletResponse($request);
    }

    public function vnpayWalletIpn(Request $request)
    {
        return $this->handleVnpWalletResponse($request, true);
    }

    private function handleVnpWalletResponse(Request $request, bool $isIpn = false)
    {
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $input = $request->all();

        Log::info('[VNPAY WALLET] Dữ liệu nhận từ VNPay', ['data' => $input]);

        $secureHash = $input['vnp_SecureHash'] ?? '';
        unset($input['vnp_SecureHash'], $input['vnp_SecureHashType']);

        ksort($input);
        $hashData = '';
        foreach ($input as $key => $value) {
            $hashData .= ($hashData ? '&' : '') . $key . '=' . urlencode($value);
        }
        $calcHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($calcHash !== $secureHash) {
            return $this->walletVnpResponse($isIpn, '97', 'Chữ ký không hợp lệ');
        }

        $transaction = \App\Models\WalletTransaction::where('transaction_code', $input['vnp_TxnRef'])->lockForUpdate()->first();

        if (!$transaction) {
            return $this->walletVnpResponse($isIpn, '01', 'Giao dịch không tồn tại');
        }


        if ($transaction->expires_at && $transaction->expires_at->isPast() && $transaction->status === 'pending') {
            $transaction->update(['status' => 'rejected']);
            Mail::to($transaction->user->email)->queue(new WalletTransactionMail($transaction, 'Giao dịch nạp tiền đã hết hạn'));
            return $this->walletVnpResponse($isIpn, '02', 'Giao dịch đã hết hạn');
        }

        if ($transaction->status === 'success') {
            return $this->walletVnpResponse($isIpn, '00', 'Giao dịch đã được xử lý trước đó', $transaction);
        }

        $respCode = $input['vnp_ResponseCode'] ?? null;

        DB::transaction(function () use ($transaction, $respCode) {
            if ($respCode === '00') {
                $transaction->update([
                    'status' => 'success',
                    'payment_url' => null
                ]);
                $transaction->wallet->increment('balance', $transaction->amount);

                Mail::to($transaction->user->email)->queue(new WalletTransactionMail($transaction, 'Nạp tiền thành công'));
            } else {

                $transaction->update(['status' => 'rejected']);
            }
        });

        return $this->walletVnpResponse(
            $isIpn,
            $respCode === '00' ? '00' : '02',
            $respCode === '00' ? 'Nạp tiền thành công' : 'Nạp tiền thất bại',
            $transaction
        );
    }

    private function walletVnpResponse(bool $isIpn, string $code, string $message, $transaction = null)
    {
        return response()->json(
            $isIpn
                ? ['RspCode' => $code, 'Message' => $message]
                : ['code' => $code, 'message' => $message, 'transaction' => $transaction]
        );
    }
}
