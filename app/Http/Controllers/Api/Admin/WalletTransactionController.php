<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Mail\WithdrawSuccessMail;
use App\Models\WalletTransaction;
use App\Mail\WithdrawRejectedMail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class WalletTransactionController extends Controller
{
    private $validTransactionTypes = [
        'deposit',
        'withdraw',
        'payment',
        'refund'
    ];

    public function index(Request $request)
    {
        $query = WalletTransaction::with(['user:id,name,email,so_dien_thoai', 'order']);


        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->whereHas('user', function ($userQuery) use ($keyword) {
                $userQuery->where('name', 'like', "%$keyword%")
                    ->orWhere('email', 'like', "%$keyword%")
                    ->orWhere('so_dien_thoai', 'like', "%$keyword%");
            });
        }

        if ($request->filled('type')) {

            if (!in_array($request->type, $this->validTransactionTypes)) {
                return response()->json([
                    'message' => 'Loại giao dịch không hợp lệ. Các loại hợp lệ: ' . implode(', ', $this->validTransactionTypes)
                ], 400);
            }
            $query->where('type', $request->type);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->latest()->paginate(10);

        return response()->json($transactions);
    }


    public function show($id)
    {
        $transaction = WalletTransaction::with(['user:id,name,email,so_dien_thoai'])->findOrFail($id);
        return response()->json($transaction);
    }


    public function updateStatus(Request $request, $id)
    {
        $transaction = WalletTransaction::findOrFail($id);
        $request->validate([
            'status' => 'required|in:success,rejected',
            'transfer_image' => 'required_if:status,success|image|mimes:jpg,jpeg,png|max:2048',
            'rejection_reason' => 'required_if:status,rejected|string|max:255',
        ], [
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'transfer_image.required_if' => 'Bạn phải tải lên ảnh minh chứng khi duyệt thành công.',
            'transfer_image.image' => 'Ảnh minh chứng phải là định dạng ảnh.',
            'transfer_image.mimes' => 'Ảnh minh chứng chỉ chấp nhận JPG, JPEG hoặc PNG.',
            'transfer_image.max' => 'Ảnh minh chứng không được vượt quá 2MB.',
            'rejection_reason.required_if' => 'Bạn phải nhập lý do từ chối khi chuyển sang thất bại.',
        ]);

        if ($transaction->type !== 'withdraw') {
            return response()->json([
                'message' => 'Chỉ có giao dịch rút tiền mới được phép cập nhật trạng thái.'
            ], 403);
        }

        $newStatus = $request->input('status');
        $rejectionReason = $request->input('rejection_reason');
        $currentStatus = $transaction->status;

        $allowedTransitions = [
            'pending' => ['success', 'rejected'],
            'success' => [],
            'rejected' => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return response()->json([
                'message' => "Không thể chuyển từ trạng thái '$currentStatus' sang '$newStatus'."
            ], 400);
        }



        DB::beginTransaction();
        try {
            $wallet = Wallet::where('user_id', $transaction->user_id)->first();
            if (!$wallet) {
                DB::rollBack();
                return response()->json(['message' => 'Không tìm thấy ví người dùng.'], 404);
            }

            if ($newStatus === 'success') {
                $path = $request->file('transfer_image')->store('transfers', 'public');
                $transaction->transfer_image = $path;

                if ($wallet->frozen_balance < $transaction->amount) {
                    DB::rollBack();
                    return response()->json(['message' => 'Số tiền đóng băng không đủ.'], 400);
                }
                $wallet->frozen_balance -= $transaction->amount;
                $wallet->save();

                // Cập nhật description
                $transaction->description = 'Rút tiền thành công';
            }

            if ($newStatus === 'rejected') {
                $transaction->rejection_reason = $rejectionReason;

                if ($wallet->frozen_balance < $transaction->amount) {
                    DB::rollBack();
                    return response()->json(['message' => 'Số tiền đóng băng không đủ để hoàn.'], 400);
                }
                $wallet->frozen_balance -= $transaction->amount;
                $wallet->balance += $transaction->amount;
                $wallet->save();

                // Cập nhật description
                $transaction->description = 'Yêu cầu rút tiền bị từ chối';
            }

            $transaction->status = $newStatus;
            $transaction->save();
            if ($newStatus === 'success') {
                Mail::to($transaction->user->email)->queue(new WithdrawSuccessMail($transaction));
            }

            if ($newStatus === 'rejected') {
                Mail::to($transaction->user->email)->queue(new WithdrawRejectedMail($transaction));
            }


            DB::commit();

            return response()->json([
                'message' => "Cập nhật trạng thái thành công",
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }
}
