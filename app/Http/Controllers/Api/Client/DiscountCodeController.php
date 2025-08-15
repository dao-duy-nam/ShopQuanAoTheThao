<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DiscountCode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DiscountCodeController extends Controller
{
    public function check(Request $request)
    {
        $data = $request->validate([
            'ma_giam_gia' => 'required|string',
            'tong_tien' => 'required|integer|min:0',
            'san_pham_id' => 'nullable|exists:san_phams,id',
        ], [
            'ma_giam_gia.required' => 'Vui lòng nhập mã giảm giá.',
            'ma_giam_gia.string' => 'Mã giảm giá không hợp lệ.',
            'tong_tien.required' => 'Vui lòng nhập tổng tiền đơn hàng.',
            'tong_tien.integer' => 'Tổng tiền phải là số.',
            'tong_tien.min' => 'Tổng tiền phải lớn hơn hoặc bằng 0.',
            'san_pham_id.exists' => 'Sản phẩm không tồn tại.',
        ]);

        // Lấy mã giảm giá từ DB
        $voucher = DiscountCode::where('ma', $data['ma_giam_gia'])->first();

        if (!$voucher) {
            return response()->json(['message' => 'Mã giảm giá không tồn tại.'], 400);
        }

        // Kiểm tra trạng thái
        if (!$voucher->trang_thai) {
            return response()->json(['message' => 'Mã giảm giá đã bị vô hiệu hóa.'], 400);
        }

        $now = Carbon::now();

        // Kiểm tra thời gian bắt đầu
        if ($voucher->ngay_bat_dau && $now->lt($voucher->ngay_bat_dau)) {
            return response()->json(['message' => 'Mã giảm giá chưa bắt đầu.'], 400);
        }

        // Kiểm tra thời gian kết thúc
        if ($voucher->ngay_ket_thuc && $now->gt($voucher->ngay_ket_thuc)) {
            return response()->json(['message' => 'Mã giảm giá đã hết hạn.'], 400);
        }

        // Kiểm tra số lượng còn lại
        // if ($voucher->so_luong <= 0) {
        //     return response()->json(['message' => 'Mã giảm giá đã hết lượt sử dụng.'], 400);
        // }

        // Kiểm tra giá trị đơn hàng tối thiểu
        if ($voucher->gia_tri_don_hang && $data['tong_tien'] < $voucher->gia_tri_don_hang) {
            return response()->json(['message' => 'Đơn hàng chưa đủ giá trị tối thiểu để áp mã.'], 400);
        }

        // Nếu mã áp dụng cho sản phẩm cụ thể
        if ($voucher->ap_dung_cho === 'san_pham') {
            if (
                !$voucher->san_pham_id ||
                empty($data['san_pham_id']) ||
                $data['san_pham_id'] != $voucher->san_pham_id
            ) {
                return response()->json(['message' => 'Mã chỉ áp dụng cho sản phẩm cụ thể.'], 400);
            }
        }

        // Tính số tiền được giảm
        if ($voucher->loai === 'phan_tram') {
            $giam = intval($data['tong_tien'] * $voucher->gia_tri / 100);
        } else {
            $giam = $voucher->gia_tri;
        }

        // Giới hạn giảm tối đa nếu có
        if (!empty($voucher->giam_toi_da)) {
            $giam = min($giam, $voucher->giam_toi_da);
        }

        // Không được giảm vượt quá tổng tiền
        $giam = min($giam, $data['tong_tien']);

        Log::info('Mã giảm giá hợp lệ', [
            'ma' => $voucher->ma,
            'user_id' => $request->user()?->id,
            'tong_tien' => $data['tong_tien'],
            'giam_gia' => $giam,
        ]);

        return response()->json([
            'message' => 'Mã hợp lệ.',
            'data' => [
                'ma' => $voucher->ma,
                'loai' => $voucher->loai,
                'gia_tri' => $voucher->gia_tri,
                'ap_dung_cho' => $voucher->ap_dung_cho,
                'san_pham_id' => $voucher->san_pham_id,
                'giam_gia' => $giam,
                'tong_phai_tra' => $data['tong_tien'] - $giam,
            ]
        ]);
    }

    public function userDiscounts(Request $request)
    {
        $user = $request->user();
        $now = now();

        if (!$user) {
            return response()->json(['message' => 'Bạn cần đăng nhập để xem mã giảm giá.'], 401);
        }
        $discounts = $user->discountCodes()
            ->where('trang_thai', true)
            // ->where('so_luong', '>', 0)
            ->where(function ($q) use ($now) {
                $q->whereNull('ngay_bat_dau')->orWhere('ngay_bat_dau', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ngay_ket_thuc')->orWhere('ngay_ket_thuc', '>=', $now);
            })
            ->where(function ($q) {
                $q->whereNull('gioi_han')
                    ->orWhereRaw('ma_giam_gia_nguoi_dung.so_lan_da_dung < ma_giam_gias.gioi_han');
            })
            ->get();

        return response()->json([
            'message' => 'Danh sách mã giảm giá còn áp dụng của bạn.',
            'data' => $discounts
        ]);
    }
}
