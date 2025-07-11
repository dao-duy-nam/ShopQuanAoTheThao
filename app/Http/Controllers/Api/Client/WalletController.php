<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\WithdrawRequest;
use App\Http\Requests\PayRequest;
use App\Http\Resources\WalletResource;
use App\Http\Resources\WalletTransactionResource;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\WalletService;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    // Lấy số dư ví
    public function getBalance(Request $request)
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $request->user()->id]);
        return new WalletResource($wallet);
    }

    // Lấy lịch sử giao dịch
    public function getTransactions(Request $request)
    {
        $wallet = Wallet::where('user_id', $request->user()->id)->firstOrFail();
        $transactions = $wallet->transactions()->latest()->paginate(20);
        return WalletTransactionResource::collection($transactions);
    }

    // Nạp tiền vào ví
    // public function deposit(DepositRequest $request)
    // {
    //     $transaction = $this->walletService->deposit($request->user(), $request->amount, $request->payment_method);
    //     // Thực tế: trả về link thanh toán, ở đây trả về transaction
    //     return new WalletTransactionResource($transaction);
    // }

    // Rút tiền từ ví
    public function withdraw(WithdrawRequest $request)
    {
        try {
            $bank_info = $request->bank_name . ' - ' . $request->bank_account;
            $transaction = $this->walletService->withdraw($request->user(), $request->amount, $bank_info);
            return new WalletTransactionResource($transaction);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // Thanh toán đơn hàng bằng ví
    public function pay(PayRequest $request)
    {
        try {
            $this->walletService->pay($request->user(), $request->order_id, $request->amount);
            return response()->json(['message' => 'Payment successful']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // Hoàn tiền vào ví
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
        'amount' => 'required|numeric|min:10000',
    ]);

    $user = $request->user();
    $wallet = $user->wallet;
    $amount = $request->amount;
    $code = 'NAP_' . time();

    // Lưu vào bảng wallet_transactions
    $wallet->transactions()->create([
        'code' => $code,
        'amount' => $amount,
        'type' => 'deposit',
        'status' => 'pending',
    ]);

    // Tạo URL thanh toán VNPAY
    $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
    $vnp_ReturnUrl = config('services.vnpay.return_url');
    $vnp_TmnCode = config('services.vnpay.tmn_code');
    $vnp_HashSecret = config('services.vnpay.hash_secret');
    $vnp_Amount = $amount * 100;

    $inputData = [
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => now()->format('YmdHis'),
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
    $query = "";
    $hashData = "";

    foreach ($inputData as $key => $value) {
        $query .= urlencode($key) . "=" . urlencode($value) . '&';
        $hashData .= $key . "=" . $value . '&';
    }

    $query = rtrim($query, '&');
    $hashData = rtrim($hashData, '&');

    $vnp_SecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
    $redirectUrl = $vnp_Url . "?" . $query . "&vnp_SecureHash=" . $vnp_SecureHash;

    return response()->json(['redirect_url' => $redirectUrl]);
}
} 