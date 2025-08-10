<?php

namespace App\Http\Controllers\Api\Client;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\PhiShip;
use App\Models\Product;
use App\Models\Variant;
use App\Models\Shipping;
use App\Models\OrderDetail;
use Illuminate\Support\Str;
use App\Models\DiscountCode;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Mail\OrderConfirmationMail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Mail\OrderStatusChangedMail;
use Illuminate\Support\Facades\Mail;
use App\Services\WalletService;

class ClientOrderController extends Controller
{

    public function payWithWallet(Request $request, $orderId)
    {
        $user = $request->user();

        $order = Order::with(['orderDetail', 'user'])->findOrFail($orderId);

        // Kiểm tra quyền sở hữu đơn hàng
        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => 'Bạn không có quyền thanh toán đơn hàng này.'
            ], 403);
        }

        // Kiểm tra trạng thái đơn hàng
        if ($order->trang_thai_thanh_toan !== 'cho_xu_ly') {
            return response()->json([
                'message' => 'Đơn hàng này không thể thanh toán do đã ở trạng thái: ' . $order->trang_thai_thanh_toan
            ], 400);
        }

        // Tìm ví của người dùng
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        // Kiểm tra số dư ví
        if ($wallet->balance < $order->so_tien_thanh_toan) {
            return response()->json([
                'message' => 'Số dư ví không đủ để thanh toán đơn hàng.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Trừ tiền từ ví
            $wallet->decrement('balance', $order->so_tien_thanh_toan);

            // Tạo giao dịch ví
            $transaction = WalletTransaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'transaction_code' => 'PAY_' . strtoupper(Str::random(6)),
                'type' => 'payment',
                'amount' => $order->so_tien_thanh_toan,
                'status' => 'success',
                'description' => 'Thanh toán đơn hàng ' . $order->ma_don_hang,
                'related_order_id' => $order->id,
            ]);

            // Cập nhật trạng thái thanh toán đơn hàng
            $order->update([
                'trang_thai_thanh_toan' => 'da_thanh_toan',
                'ngay_thanh_toan' => now(),
                'expires_at' => null,
                'payment_link' => null,
            ]);

            // Gửi email thông báo
            $message = 'Đơn hàng ' . $order->ma_don_hang . ' đã được thanh toán thành công bằng ví.';
            Mail::to($order->email_nguoi_dat)->send(new OrderStatusChangedMail($order, $message));

            DB::commit();

            return response()->json([
                'message' => 'Thanh toán đơn hàng thành công bằng ví.',
                'order' => $order,
                'transaction' => $transaction
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi thanh toán bằng ví', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Lỗi khi thanh toán bằng ví: ' . $e->getMessage()
            ], 500);
        }
    }

    // Các phương thức khác giữ nguyên

    public function checkPendingPayment(Request $request)
    {
        $user = $request->user();

        $order = Order::where('user_id', $user->id)
            ->where('trang_thai_thanh_toan', 'cho_xu_ly')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($order) {
            return response()->json([
                'status'       => 'need_payment',
                'message'      => 'Bạn có một đơn hàng còn hiệu lực chưa thanh toán.',
                'ma_don_hang'  => $order->ma_don_hang,
                'payment_link' => $order->payment_link,
                'expires_at'   => $order->expires_at,
                'amount'       => $order->tong_tien,
            ]);
        }

        return response()->json([
            'status'  => 'ok',
            'message' => 'Không có đơn hàng nào cần thanh toán lại.'
        ]);
    }

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
            'ma_giam_gia' => 'nullable|string',
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
            $diaChiDayDu = trim(implode(', ', array_filter([
                $diaChi,
                $xa,
                $huyen,
                $thanhPho,
            ])));
            $phiShip = Shipping::where('tinh_thanh', $thanhPho)->value('phi') ?? 30000;

            $tongTienDonHang = 0;
            $chiTietSanPham = [];

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

                'dia_chi_day_du' => $diaChiDayDu,
                'phi_ship' => $phiShip,

                'ma_giam_gia_id' => $discountId ?? null,
                'ma_giam_gia' => $validated['ma_giam_gia'] ?? null,

            ]);

            $items = $validated['items'] ?? null;

            if ($items) {
                foreach ($items as $item) {
                    $soLuong = $item['so_luong'];
                    $bienTheId = $item['bien_the_id'] ?? null;

                    if ($bienTheId) {
                        $bienThe = Variant::with(['product', 'variantAttributes.attributeValue.attribute'])->findOrFail($bienTheId);
                        if ($bienThe->so_luong < $soLuong) throw new \Exception("Sản phẩm không đủ tồn kho.");
                        $donGia = $bienThe->gia_khuyen_mai ?? $bienThe->gia;
                        if (is_null($donGia)) {
                            throw new \Exception("Biến thể '{$bienThe->product->ten}' không có giá.");
                        }
                        $tongTien = $donGia * $soLuong;
                        $tongTienDonHang += $tongTien;

                        $thuocTinhBienThe = $bienThe->variantAttributes->map(function ($attr) {
                            return [
                                'thuoc_tinh' => $attr->attributeValue->attribute->ten ?? '',
                                'gia_tri' => $attr->attributeValue->gia_tri ?? ''
                            ];
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
                        $chiTietSanPham[] = [
                            'san_pham_id' => $bienThe->product->id,
                            'ten_san_pham' => $bienThe->product->ten,
                            'so_luong' => $soLuong,
                            'don_gia' => $donGia,
                            'tong_tien' => $tongTien,
                            'thuoc_tinh_bien_the' => $thuocTinhBienThe,
                            'hinh_anh' => $bienThe->hinh_anh ?? $bienThe->product->hinh_anh, // THÊM DÒNG NÀY
                        ];
                    } else {
                        $sanPham = Product::findOrFail($item['san_pham_id']);
                        if ($sanPham->so_luong < $soLuong) throw new \Exception("Sản phẩm '{$sanPham->ten}' không đủ tồn kho.");
                        $donGia = $sanPham->gia_khuyen_mai ?? $sanPham->gia;
                        if (is_null($donGia)) {
                            throw new \Exception("Sản phẩm '{$sanPham->ten}' không có giá.");
                        }
                        $tongTien = $donGia * $soLuong;
                        $tongTienDonHang += $tongTien;

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
                        $chiTietSanPham[] = [
                            'san_pham_id' => $sanPham->id,
                            'ten_san_pham' => $sanPham->ten,
                            'so_luong' => $soLuong,
                            'don_gia' => $donGia,
                            'tong_tien' => $tongTien,
                            'thuoc_tinh_bien_the' => null,
                            'hinh_anh' => $bienTheId
                                ? ($bienThe->hinh_anh ?? $sanPham->hinh_anh)
                                : $sanPham->hinh_anh,
                        ];
                    }
                }
            } else {
                $gioHang = DB::table('gio_hangs')->where('user_id', $user->id)->first();
                if (!$gioHang) throw new \Exception('Không tìm thấy giỏ hàng.');
                $cartItems = DB::table('chi_tiet_gio_hangs')->where('gio_hang_id', $gioHang->id)->get();
                if ($cartItems->isEmpty()) throw new \Exception('Giỏ hàng đang trống.');

                foreach ($cartItems as $item) {
                    $soLuong = $item->so_luong;
                    $bienTheId = $item->bien_the_id ?? null;

                    if ($bienTheId) {
                        $bienThe = Variant::with(['product', 'variantAttributes.attributeValue.attribute'])->findOrFail($bienTheId);
                        if ($bienThe->so_luong < $soLuong) throw new \Exception("Biến thể không đủ tồn kho.");
                        $donGia = $bienThe->gia_khuyen_mai ?? $bienThe->gia;
                        if (is_null($donGia)) {
                            throw new \Exception("Biến thể '{$bienThe->product->ten}' không có giá.");
                        }
                        $tongTien = $donGia * $soLuong;
                        $tongTienDonHang += $tongTien;

                        $thuocTinhBienThe = $bienThe->variantAttributes->map(function ($attr) {
                            return [
                                'thuoc_tinh' => $attr->attributeValue->attribute->ten ?? '',
                                'gia_tri' => $attr->attributeValue->gia_tri ?? ''
                            ];
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
                        $chiTietSanPham[] = [
                            'san_pham_id' => $bienThe->product->id,
                            'ten_san_pham' => $bienThe->product->ten,
                            'so_luong' => $soLuong,
                            'don_gia' => $donGia,
                            'tong_tien' => $tongTien,
                            'thuoc_tinh_bien_the' => $thuocTinhBienThe,
                        ];
                    } else {
                        $sanPham = Product::findOrFail($item->san_pham_id);
                        if ($sanPham->so_luong < $soLuong) throw new \Exception("Sản phẩm '{$sanPham->ten}' không đủ tồn kho.");
                        $donGia = $sanPham->gia_khuyen_mai ?? $sanPham->gia;
                        if (is_null($donGia)) {
                            throw new \Exception("Sản phẩm '{$sanPham->ten}' không có giá.");
                        }
                        $tongTien = $donGia * $soLuong;
                        $tongTienDonHang += $tongTien;

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
                        $chiTietSanPham[] = [
                            'san_pham_id' => $sanPham->id,
                            'ten_san_pham' => $sanPham->ten,
                            'so_luong' => $soLuong,
                            'don_gia' => $donGia,
                            'tong_tien' => $tongTien,
                            'thuoc_tinh_bien_the' => null,
                            'hinh_anh' => $bienTheId
                                ? ($bienThe->hinh_anh ?? $sanPham->hinh_anh)
                                : $sanPham->hinh_anh,
                        ];
                    }
                }

                DB::table('chi_tiet_gio_hangs')->where('gio_hang_id', $gioHang->id)->delete();
                DB::table('gio_hangs')->where('id', $gioHang->id)->delete();
            }

            $giamGia = 0;
            $discountId = null;

            if (!empty($validated['ma_giam_gia'])) {
                $discount = DiscountCode::where('ma', $validated['ma_giam_gia'])
                    ->where('trang_thai', 1)
                    ->where(function ($query) {
                        $query->whereNull('ngay_bat_dau')->orWhere('ngay_bat_dau', '<=', now());
                    })
                    ->where(function ($query) {
                        $query->whereNull('ngay_ket_thuc')->orWhere('ngay_ket_thuc', '>=', now());
                    })
                    ->first();
                // Kiểm tra số lần user đã dùng mã này
                if (!$discount) {
                    throw new \Exception("Mã giảm giá không hợp lệ hoặc đã hết hạn.");
                }

                // Check số lần đã dùng
                $discountUser = DB::table('ma_giam_gia_nguoi_dung')
                    ->where('ma_giam_gia_id', $discount->id)
                    ->where('nguoi_dung_id', $user->id)
                    ->first();

                if ($discount->gioi_han !== null) {
                    $soLanDaDung = $discountUser?->so_lan_da_dung ?? 0;
                    if ($soLanDaDung >= $discount->gioi_han) {
                        throw new \Exception("Bạn đã sử dụng mã này quá số lần cho phép.");
                    }
                }

                if (!$discount) {
                    throw new \Exception("Mã giảm giá không hợp lệ hoặc đã hết hạn.");
                }

                if ($discount->so_luong !== null && $discount->so_luong <= 0) {
                    throw new \Exception("Mã giảm giá đã được sử dụng hết số lượt.");
                }

                if ($discount->gia_tri_don_hang && $tongTienDonHang < $discount->gia_tri_don_hang) {
                    throw new \Exception("Đơn hàng chưa đạt mức tối thiểu để áp dụng mã.");
                }

                if ($discount->ap_dung_cho === 'san_pham' && $discount->san_pham_id !== null) {
                    $apDungSanPham = collect($chiTietSanPham)->contains(function ($item) use ($discount) {
                        return (int)$item['san_pham_id'] === (int)$discount->san_pham_id;
                    });

                    if (!$apDungSanPham) {
                        throw new \Exception("Mã giảm giá chỉ áp dụng cho sản phẩm cụ thể và không áp dụng cho đơn hàng này.");
                    }
                }

                if ($discount->loai === 'phan_tram') {
                    $giamGia = ($tongTienDonHang * $discount->gia_tri) / 100;
                    if ($discount->gia_tri_toi_da !== null) {
                        $giamGia = min($giamGia, $discount->gia_tri_toi_da);
                    }
                } elseif ($discount->loai === 'tien') {
                    $giamGia = $discount->gia_tri;
                }

                $giamGia = min($giamGia, $tongTienDonHang);
                $discount->decrement('so_luong');

                DB::table('ma_giam_gia_nguoi_dung')->updateOrInsert(
                    [
                        'ma_giam_gia_id' => $discount->id,
                        'nguoi_dung_id' => $user->id,
                    ],
                    [
                        'so_lan_da_dung' => ($discountUser?->so_lan_da_dung ?? 0) + 1,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $discountId = $discount->id;
            }

            $soTienPhaiTra = $tongTienDonHang - $giamGia + $phiShip;

            if ($giamGia > 0 && $tongTienDonHang > 0) {
                $tongGiamDaPhanBo = 0;
                $soLuongSanPham = count($chiTietSanPham);

                foreach ($chiTietSanPham as $index => &$sp) {
                    $tyLe = $sp['tong_tien'] / $tongTienDonHang;
                    $giamTru = round($tyLe * $giamGia);

                    if ($index === $soLuongSanPham - 1) {
                        $giamTru = $giamGia - $tongGiamDaPhanBo;
                    }

                    $tongGiamDaPhanBo += $giamTru;
                    $tongSauGiam = max(0, $sp['tong_tien'] - $giamTru);

                    // Cập nhật vào bảng chi_tiet_don_hangs
                    DB::table('chi_tiet_don_hangs')
                        ->where('don_hang_id', $order->id)
                        ->where('san_pham_id', $sp['san_pham_id'])
                        ->where('so_luong', $sp['so_luong']) // Cần xác định rõ nếu có trùng sản phẩm
                        ->update([
                            'ma_giam_gia_id' => $discountId,
                            'ma_giam_gia' => $validated['ma_giam_gia'] ?? null,
                            'so_tien_duoc_giam' => $giamTru,
                        ]);

                    $sp = [
                        'san_pham_id' => $sp['san_pham_id'],
                        'ten_san_pham' => $sp['ten_san_pham'],
                        'so_luong' => $sp['so_luong'],
                        'ma_giam_gia_id' => $discountId,
                        'ma_giam_gia' => $validated['ma_giam_gia'] ?? null,
                        'don_gia' => $sp['don_gia'],
                        'tong_tien' => $sp['tong_tien'],
                        'so_tien_duoc_giam' => $tongSauGiam,
                        'thuoc_tinh_bien_the' => $sp['thuoc_tinh_bien_the'],
                        'hinh_anh' => $sp['hinh_anh'], // THÊM DÒNG NÀY
                        'bien_the_id' => $sp['bien_the_id'] ?? null, // Thêm dòng này

                    ];
                }
                unset($sp);
            }



            $tenSanPhamTongHop = collect($chiTietSanPham)
                ->pluck('ten_san_pham')
                ->unique()
                ->implode(', ');

            // Lấy thuoc_tinh_bien_the trực tiếp từ bảng chi_tiet_don_hangs
            $thuocTinhBienTheTongHop = DB::table('chi_tiet_don_hangs')
                ->where('don_hang_id', $order->id)
                ->whereNotNull('thuoc_tinh_bien_the')
                ->pluck('thuoc_tinh_bien_the')
                ->flatMap(function ($json) {
                    $decoded = json_decode($json, true);
                    if (is_array($decoded)) {
                        return collect($decoded)->pluck('gia_tri')->filter();
                    }
                    return [];
                })
                ->unique()
                ->implode(', ');

            $order->update([
                'so_tien_thanh_toan' => $soTienPhaiTra,
                'ma_giam_gia_id' => $discountId,
                'ma_giam_gia' => $validated['ma_giam_gia'] ?? null,
                'so_tien_duoc_giam' => $giamGia,
                'ten_san_pham' => $tenSanPhamTongHop,
                'gia_tri_bien_the' => $thuocTinhBienTheTongHop,
                'dia_chi_day_du' => $diaChiDayDu,
            ]);

            foreach ($chiTietSanPham as $sp) {
                if (!empty($sp['thuoc_tinh_bien_the']) && !empty($sp['bien_the_id'])) {
                    $bienThe = Variant::find($sp['bien_the_id']);
                    if (!$bienThe || $bienThe->so_luong < $sp['so_luong']) {
                        throw new \Exception("Biến thể '{$sp['ten_san_pham']}' không còn đủ tồn kho để hoàn tất đơn hàng.");
                    }
                }
            }


            DB::commit();

            Mail::to($emailNguoiDat)->send(new OrderConfirmationMail($order));

            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'order' => $order,
                'ma_giam_gia' => $validated['ma_giam_gia'] ?? null,
                'tong_tien' => $tongTienDonHang,
                'giam_gia' => $giamGia,
                'phi_ship' => $order->phi_ship,
                'phai_tra' => $soTienPhaiTra,
                'chi_tiet_san_pham' => $chiTietSanPham,
            ], 200);
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

            // Kiểm tra quyền truy cập
            if ($order->user_id !== $user->id) {
                return response()->json(['error' => 'Bạn không có quyền xem đơn hàng này.'], 403);
            }
            // Xử lý chi tiết từng sản phẩm trong đơn hàng
            $orderDetails = $order->orderDetail->map(function ($detail) {
                // Lấy thuộc tính biến thể (nếu có)
                $variantAttributes = $detail->variant && $detail->variant->variantAttributes
                    ? $detail->variant->variantAttributes->map(function ($attr) {
                        return [
                            ...$attr->getAttributes(),
                            'attribute_value' => $attr->attributeValue ? array_merge(
                                $attr->attributeValue->getAttributes(),
                                [
                                    'attribute' => $attr->attributeValue->attribute
                                        ? $attr->attributeValue->attribute->toArray()
                                        : null
                                ]
                            ) : null
                        ];
                    })->toArray()
                    : [];

                return array_merge(
                    $detail->getAttributes(),
                    [
                        'product' => $detail->product ? $detail->product->toArray() : null,
                        'variant' => $detail->variant ? array_merge(
                            $detail->variant->getAttributes(),
                            ['thuoc_tinh_bien_the' => $variantAttributes]
                        ) : null
                    ]
                );
            });

            // Trả toàn bộ thông tin đơn hàng
            return response()->json([
                'order' => array_merge(
                    $order->getAttributes(),
                    [
                        'user' => $order->user ? $order->user->toArray() : null,
                        'phuong_thuc_thanh_toan' => $order->paymentMethod ? $order->paymentMethod->toArray() : null,
                        'items' => $orderDetails
                    ]
                )
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
                    'hinh_anh' => $detail->variant && $detail->variant->hinh_anh
                        ? $detail->variant->hinh_anh
                        : optional($detail->product)->hinh_anh,
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
               'gia_tri_bien_the' => $order->gia_tri_bien_the,
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

    public function huyDon(Request $request, $id, WalletService $walletService)
    {
        $validated = $request->validate([
            'ly_do_huy' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::with(['user', 'orderDetail.variant'])->findOrFail($id);

            // Lưu trạng thái thanh toán trước khi cập nhật
            $oldPaymentStatus = $order->trang_thai_thanh_toan;

            foreach ($order->orderDetail as $chiTiet) {
                $variant = $chiTiet->variant;
                if ($variant) {
                    $variant->so_luong += $chiTiet->so_luong;
                    $variant->save();
                }
            }

            $order->trang_thai_don_hang = 'da_huy';
            if (in_array($order->phuong_thuc_thanh_toan_id, [2, 3])) {
                $order->trang_thai_thanh_toan = 'cho_hoan_tien';
            } else if ($order->phuong_thuc_thanh_toan_id == 1) {
                $order->trang_thai_thanh_toan = 'da_huy';
            } else {
                $order->trang_thai_thanh_toan = 'da_huy';
            }

            $order->ly_do_huy = $validated['ly_do_huy'];
            $order->save();

            // Hoàn tiền về ví nếu thanh toán VNPay và đã thanh toán thành công
            if (
                (int) $order->phuong_thuc_thanh_toan_id === 2 &&
                $oldPaymentStatus === 'da_thanh_toan' &&
                !$order->refund_done
            ) {
                Log::info('[CLIENT CANCEL] Refund to wallet', [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'amount' => $order->so_tien_thanh_toan,
                    'payment_method_id' => $order->phuong_thuc_thanh_toan_id,
                ]);
                $walletService->refund($order->user, $order->id, $order->so_tien_thanh_toan);
            }

            // Hoàn khi đơn đã thanh toán bằng ví nội bộ (phát hiện qua giao dịch 'payment' thành công)
            if ($oldPaymentStatus === 'da_thanh_toan' && !$order->refund_done) {
                $hasWalletPayment = WalletTransaction::where('user_id', $order->user_id)
                    ->where('related_order_id', $order->id)
                    ->where('type', 'payment')
                    ->where('status', 'success')
                    ->exists();

                if ($hasWalletPayment) {
                    Log::info('[CLIENT CANCEL] Refund internal wallet payment', [
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                        'amount' => $order->so_tien_thanh_toan,
                    ]);
                    $walletService->refund($order->user, $order->id, $order->so_tien_thanh_toan);
                    if ($order->trang_thai_thanh_toan !== 'hoan_tien') {
                        $order->update(['trang_thai_thanh_toan' => 'hoan_tien']);
                    }
                }
            }

            $message = 'Đơn hàng của bạn đã bị hủy. Lý do: ' . $validated['ly_do_huy'];
            Mail::to($order->email_nguoi_dat)->send(new OrderStatusChangedMail($order, $message));

            DB::commit();

            return response()->json([
                'message' => 'Đơn hàng đã được hủy thành công. Lý do: ' . $validated['ly_do_huy'],
                'order' => $order
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Đã xảy ra lỗi khi huỷ đơn hàng.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function traHang(Request $request, $id)
    {
        $validated = $request->validate([
            'ly_do_tra_hang' => 'required|string|max:255',
            'hinh_anh_tra_hang' => 'nullable|array',
            'hinh_anh_tra_hang.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $order = Order::with(['user', 'orderDetail.variant'])->findOrFail($id);
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => 'Bạn không có quyền trả đơn hàng này.'
            ], 403);
        }

        if (!in_array($order->trang_thai_don_hang, ['da_giao', 'da_nhan'])) {
            return response()->json([
                'message' => 'Chỉ được trả khi đơn hàng đã giao hoặc đã nhận.'
            ], 400);
        }

        if ($order->trang_thai_don_hang === 'da_nhan') {
            if (!$order->thoi_gian_nhan) {
                return response()->json([
                    'message' => 'Không xác định được thời gian nhận hàng.'
                ], 400);
            }

            $thoiGianNhan = $order->thoi_gian_nhan instanceof Carbon
                ? $order->thoi_gian_nhan
                : Carbon::parse($order->thoi_gian_nhan);

            if ($thoiGianNhan->addDays(3)->lt(now())) {
                return response()->json([
                    'message' => 'Đơn hàng đã nhận quá 3 ngày, không thể trả hàng.'
                ], 400);
            }
        }

        DB::beginTransaction();

        try {
            $order->trang_thai_don_hang = 'yeu_cau_tra_hang';
            $order->trang_thai_thanh_toan = 'cho_hoan_tien';
            $order->ly_do_tra_hang = $validated['ly_do_tra_hang'];

            // Xử lý upload hình ảnh
            $imagePaths = [];
            if ($request->hasFile('hinh_anh_tra_hang')) {
                foreach ($request->file('hinh_anh_tra_hang') as $image) {
                    $path = $image->store('tra_hang', 'public');
                    $imagePaths[] = $path;
                }
                $order->hinh_anh_tra_hang = json_encode($imagePaths);
            }

            $order->save();

            // Cộng lại số lượng vào tồn kho
            foreach ($order->orderDetail as $chiTiet) {
                $variant = $chiTiet->variant;
                if ($variant) {
                    $variant->so_luong += $chiTiet->so_luong;
                    $variant->save();
                }
            }

            // Gửi mail thông báo
            $message = 'Đơn hàng của bạn đã được yêu cầu trả hàng. Lý do: ' . $validated['ly_do_tra_hang'] . '. Chúng tôi sẽ xử lý hoàn tiền sớm nhất.';
            Mail::to($order->email_nguoi_dat)->send(new OrderStatusChangedMail($order, $message));

            DB::commit();

            return response()->json([
                'message' => 'Yêu cầu trả hàng đã được gửi thành công.',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Đã xảy ra lỗi khi gửi yêu cầu trả hàng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function daGiao($id)
    {
        $order = Order::with('user')->findOrFail($id);
        $user = request()->user();

        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => 'Bạn không có quyền xác nhận đơn hàng này.'
            ], 403);
        }

        if ($order->trang_thai_don_hang !== 'da_giao') {
            return response()->json([
                'message' => 'Chỉ xác nhận khi đơn hàng đã được giao.'
            ], 400);
        }

        $order->trang_thai_don_hang = 'da_nhan';
        $order->thoi_gian_nhan = now();
        $order->save();

        $message = 'Cảm ơn bạn đã xác nhận đã nhận hàng. Chúc bạn hài lòng!';
        Mail::to($order->email_nguoi_dat)->send(new OrderStatusChangedMail($order, $message));

        return response()->json([
            'message' => 'Bạn đã xác nhận đã nhận hàng thành công.',
            'order' => $order
        ]);
    }
}
