<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusChangedMail;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Variant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ClientOrderController extends Controller
{

public function store(Request $request)
{
    $validated = $request->validate([
        'phuong_thuc_thanh_toan_id' => 'required|exists:phuong_thuc_thanh_toans,id',
        'items' => 'nullable|array|min:1',
        'items.*.san_pham_id' => 'required_with:items|exists:san_phams,id',
        'items.*.so_luong' => 'required_with:items|integer|min:1',
        'items.*.bien_the_id' => 'nullable|exists:bien_thes,id',
        'dia_chi' => 'nullable|string',
        'so_dien_thoai' => 'nullable|string',
        'thanh_pho' => 'nullable|string',
        'huyen' => 'nullable|string',
        'xa' => 'nullable|string',
        'ten_nguoi_dat' => 'nullable|string|max:255',
        'email_nguoi_dat' => 'nullable|email',
        'sdt_nguoi_dat' => 'nullable|string|max:20',
    ]);

    $user = $request->user();

    if ($user->vai_tro_id !== 2) {
        return response()->json(['error' => 'Bạn không có quyền đặt hàng.'], 403);
    }

    DB::beginTransaction();

    try {
        $diaChi = $validated['dia_chi'] ?? $user->dia_chi;
        $soDienThoai = $validated['so_dien_thoai'] ?? $user->so_dien_thoai;
        $thanhPho = $validated['thanh_pho'] ?? null;
        $huyen = $validated['huyen'] ?? null;
        $xa = $validated['xa'] ?? null;
        $tenNguoiDat = trim($validated['ten_nguoi_dat'] ?? '') ?: $user->name;
        $emailNguoiDat = trim($validated['email_nguoi_dat'] ?? '') ?: $user->email;
        $sdtNguoiDat = trim($validated['sdt_nguoi_dat'] ?? '') ?: $user->so_dien_thoai;

        $tongTienDonHang = 0;
        $tenSanPhamList = [];

        $order = Order::create([
            'ma_don_hang' => 'DH' . strtoupper(Str::random(6)),
            'user_id' => $user->id,
            'phuong_thuc_thanh_toan_id' => $validated['phuong_thuc_thanh_toan_id'],
            'trang_thai_don_hang' => 'cho_xac_nhan',
            'trang_thai_thanh_toan' => 'cho_xu_ly',
            'dia_chi' => $diaChi,
            'thanh_pho' => $thanhPho,
            'huyen' => $huyen,
            'xa' => $xa,
            'ten_nguoi_dat' => $tenNguoiDat,
            'so_tien_thanh_toan' => 0,
            'email_nguoi_dat' => $emailNguoiDat,
            'sdt_nguoi_dat' => $sdtNguoiDat,
        ]);

        $items = $validated['items'] ?? null;
        if (!$items) {
            $gioHang = DB::table('gio_hangs')->where('user_id', $user->id)->first();
            if (!$gioHang) throw new \Exception('Không tìm thấy giỏ hàng.');
            $items = DB::table('chi_tiet_gio_hangs')->where('gio_hang_id', $gioHang->id)->get()->toArray();
            if (empty($items)) throw new \Exception('Giỏ hàng đang trống.');
        }

        foreach ($items as $item) {
            $soLuong = $item['so_luong'] ?? $item->so_luong;
            $bienTheId = $item['bien_the_id'] ?? ($item->bien_the_id ?? null);
            $sanPhamId = $item['san_pham_id'] ?? ($item->san_pham_id ?? null);

            if ($bienTheId) {
                $bienThe = Variant::with(['product', 'variantAttributes.attributeValue.attribute'])->findOrFail($bienTheId);
                if ($bienThe->so_luong < $soLuong) throw new \Exception("Biến thể không đủ tồn kho.");

                $donGia = $bienThe->gia_khuyen_mai ?? $bienThe->gia;
                if (is_null($donGia)) throw new \Exception("Biến thể '{$bienThe->product->ten}' không có giá.");

                $tongTien = $donGia * $soLuong;
                $tongTienDonHang += $tongTien;

                $tenSanPhamList[] = $bienThe->product->ten;

                $thuocTinhBienThe = $bienThe->variantAttributes->map(function ($attr) {
                    return $attr->attributeValue->gia_tri ?? '';
                })->filter()->values();

                OrderDetail::create([
                    'don_hang_id' => $order->id,
                    'san_pham_id' => $bienThe->product->id,
                    'bien_the_id' => $bienTheId,
                    'so_luong' => $soLuong,
                    'don_gia' => $donGia,
                    'tong_tien' => $tongTien,
                    'thuoc_tinh_bien_the' => $thuocTinhBienThe->isEmpty() ? null : json_encode($thuocTinhBienThe),
                ]);

                $bienThe->decrement('so_luong', $soLuong);
                $bienThe->increment('so_luong_da_ban', $soLuong);

            } else {
                $sanPham = Product::findOrFail($sanPhamId);
                if ($sanPham->so_luong < $soLuong) throw new \Exception("Sản phẩm '{$sanPham->ten}' không đủ tồn kho.");

                $donGia = $sanPham->gia_khuyen_mai ?? $sanPham->gia;
                if (is_null($donGia)) throw new \Exception("Sản phẩm '{$sanPham->ten}' không có giá.");

                $tongTien = $donGia * $soLuong;
                $tongTienDonHang += $tongTien;

                $tenSanPhamList[] = $sanPham->ten;

                OrderDetail::create([
                    'don_hang_id' => $order->id,
                    'san_pham_id' => $sanPham->id,
                    'bien_the_id' => null,
                    'so_luong' => $soLuong,
                    'don_gia' => $donGia,
                    'tong_tien' => $tongTien,
                    'thuoc_tinh_bien_the' => null,
                ]);

                $sanPham->decrement('so_luong', $soLuong);
                $sanPham->increment('so_luong_da_ban', $soLuong);
            }
        }

        // Nếu mua qua giỏ hàng, xoá giỏ hàng sau khi đặt
        if (!$validated['items']) {
            DB::table('chi_tiet_gio_hangs')->where('gio_hang_id', $gioHang->id)->delete();
            DB::table('gio_hangs')->where('id', $gioHang->id)->delete();
        }

        // ✅ Lấy tất cả giá trị `thuoc_tinh_bien_the` từ bảng chi_tiet_don_hangs
        $thuocTinhDonHang = OrderDetail::where('don_hang_id', $order->id)
            ->pluck('thuoc_tinh_bien_the')
            ->filter()
            ->map(function ($json) {
                $arr = json_decode($json, true);
                return is_array($arr) ? $arr : [];
            })
            ->flatten()
            ->unique()
            ->implode(' | ');

        // Cập nhật lại đơn hàng
        $order->update([
            'so_tien_thanh_toan' => $tongTienDonHang,
            'ten_san_pham' => implode(', ', $tenSanPhamList),
            'gia_tri_bien_the' => $thuocTinhDonHang ?: null,
        ]);

        DB::commit();

        Mail::to($emailNguoiDat)->send(new OrderConfirmationMail($order));

        $order = $order->fresh([
            'orderDetail.product',
            'orderDetail.variant.variantAttributes.attributeValue.attribute',
            'paymentMethod',
            'user',
        ]);

        return response()->json([
            'message' => 'Đặt hàng thành công!',
            'order' => $order->toArray(),
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Lỗi đặt hàng', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return response()->json(['error' => 'Lỗi: ' . $e->getMessage()], 500);
    }
}




public function show($id)
{
    try {
        $user = request()->user();

        $order = Order::with([
            'orderDetail.product',
            'orderDetail.variant.variantAttributes.attributeValue.attribute',
            'paymentMethod',
            'user'
        ])->findOrFail($id);

        if ($order->user_id !== $user->id) {
            return response()->json(['error' => 'Bạn không có quyền xem đơn hàng này.'], 403);
        }

        // Lấy tất cả chi tiết sản phẩm
        $chiTietSanPham = $order->orderDetail->map(function ($detail) {
            // Lấy thông tin thuộc tính biến thể từ cột đã lưu sẵn
            $thuocTinhBienThe = null;
            if (!empty($detail->thuoc_tinh_bien_the)) {
                $thuocTinhBienThe = collect(json_decode($detail->thuoc_tinh_bien_the, true))
                    ->map(fn($value) => ['gia_tri' => $value])
                    ->values();
            }

            return [
                'ten_san_pham' => optional($detail->product)->ten,
                'hinh_anh' => optional($detail->product)->hinh_anh,
                'so_luong' => $detail->so_luong,
                'don_gia' => $detail->don_gia,
                'tong_tien' => $detail->tong_tien,
                'thuoc_tinh_bien_the' => $thuocTinhBienThe,
            ];
        });

        return response()->json([
            'id' => $order->id,
            'ma_don_hang' => $order->ma_don_hang,
            'tong_tien' => $order->so_tien_thanh_toan,
            'ten_san_pham' => $order->ten_san_pham,
            'gia_tri_bien_the' => $order->gia_tri_bien_the,
            'dia_chi' => $order->dia_chi,
            'thanh_pho' => $order->thanh_pho,
            'huyen' => $order->huyen,
            'xa' => $order->xa,
            'ten_nguoi_dat' => $order->ten_nguoi_dat,
            'email_nguoi_dat' => $order->email_nguoi_dat,
            'sdt_nguoi_dat' => $order->sdt_nguoi_dat,
            'phuong_thuc_thanh_toan' => optional($order->paymentMethod)->ten,
            'trang_thai_don_hang' => $order->trang_thai_don_hang,
            'trang_thai_thanh_toan' => $order->trang_thai_thanh_toan,
            'ngay_dat' => $order->created_at->toDateTimeString(),
            'chi_tiet_san_pham' => $chiTietSanPham,
        ]);
    } catch (\Exception $e) {
        Log::error('Lỗi lấy chi tiết đơn hàng', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json(['error' => 'Lỗi: ' . $e->getMessage()], 500);
    }
}


    public function index()
    {
        $user = request()->user();

        $orders = Order::with([
            'orderDetail.product',
            'orderDetail.variant.variantAttributes.attributeValue.attribute',
            'paymentMethod'
        ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $result = $orders->map(function ($order) {
            $items = $order->orderDetail->map(function ($detail) {
                $thuocTinhBienThe = null;

                if ($detail->bien_the_id && $detail->variant && $detail->variant->variantAttributes) {
                    $thuocTinhBienThe = $detail->variant->variantAttributes->map(function ($attr) {
                        if (!$attr->attributeValue || !$attr->attributeValue->attribute) {
                            return null;
                        }
                        return [
                            'thuoc_tinh_id' => $attr->attributeValue->attribute->id,
                            'ten_thuoc_tinh' => $attr->attributeValue->attribute->ten,
                            'gia_tri' => $attr->attributeValue->gia_tri,
                        ];
                    })->filter()->values();
                }

                return [
                    'san_pham_id' => $detail->san_pham_id,
                    'ten_san_pham' => optional($detail->product)->ten,
                    'hinh_anh' => optional($detail->product)->hinh_anh,
                    'bien_the_id' => $detail->bien_the_id,
                    'thuoc_tinh_bien_the' => $thuocTinhBienThe,
                    'so_luong' => $detail->so_luong,
                    'don_gia' => $detail->don_gia,
                    'tong_tien' => $detail->tong_tien,
                ];
            });

            return [
                'id' => $order->id,
                'ma_don_hang' => $order->ma_don_hang,
                'trang_thai_don_hang' => $order->trang_thai_don_hang,
                'trang_thai_thanh_toan' => $order->trang_thai_thanh_toan,
                'tong_tien_thanh_toan' => $order->so_tien_thanh_toan,
                'ngay_dat' => $order->created_at->toDateTimeString(),
                'phuong_thuc_thanh_toan' => optional($order->paymentMethod)->ten,
                'so_luong_mat_hang' => $order->orderDetail->sum('so_luong'),
                'items' => $items,
            ];
        });

        return response()->json([
            'orders' => $result,
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ]
        ]);
    }


    public function huyDon($id)
    {
        $order = Order::with('user')->findOrFail($id);
        $order->trang_thai_don_hang = 'yeu_cau_huy_hang';
        $order->trang_thai_thanh_toan = 'da_huy';
        $order->save();

        // Gửi mail
        $message = 'Đơn hàng của bạn đã bị hủy xin hãy chờ xác nhận từ người bán.';
        Mail::to($order->user->email)->send(new OrderStatusChangedMail($order, $message));

        return response()->json([
            'message' => 'Đơn hàng đã được hủy thành công xin hãy chờ xác nhận từ người bán.',
            'order' => $order
        ]);
    }

    public function traHang($id)
    {
        $order = Order::with('user')->findOrFail($id);
        $order->trang_thai_don_hang = 'yeu_cau_tra_hang';
        $order->trang_thai_thanh_toan = 'hoan_tien';
        $order->save();

        // Gửi mail
        $message = 'Đơn hàng của bạn đã được xử lý trả hàng. Chúng tôi sẽ hoàn tiền sớm nhất.';
        Mail::to($order->user->email)->send(new OrderStatusChangedMail($order, $message));

        return response()->json([
            'message' => 'Đơn hàng đã được trả hàng thành công.',
            'order' => $order
        ]);
    }

    public function daGiao($id)
{
    $order = Order::with('user')->findOrFail($id);
    $user = request()->user();

    // Kiểm tra quyền: chỉ chủ đơn hàng mới được xác nhận
    if ($order->user_id !== $user->id) {
        return response()->json([
            'message' => 'Bạn không có quyền xác nhận đơn hàng này.'
        ], 403);
    }

    // Chỉ cho phép xác nhận nếu đang vận chuyển
    if ($order->trang_thai_don_hang !== 'dang_van_chuyen') {
        return response()->json([
            'message' => 'Chỉ có thể xác nhận khi đơn hàng đang được giao.'
        ], 400);
    }

    $order->trang_thai_don_hang = 'da_giao';
    $order->save();

    // Gửi mail thông báo
    $message = 'Bạn đã xác nhận đã nhận hàng thành công.';
    Mail::to($order->user->email)->send(new OrderStatusChangedMail($order, $message));

    return response()->json([
        'message' => 'Đã xác nhận đơn hàng đã được giao thành công.',
        'order' => $order
    ]);
}

}
