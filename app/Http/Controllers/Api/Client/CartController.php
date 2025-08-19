<?php

namespace App\Http\Controllers\Api\Client;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Variant;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Mail\CartItemIssueMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CartController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $gioHang = $user->cart;

            if (!$gioHang) {
                return response()->json([
                    'message' => 'Giỏ hàng trống',
                    'data' => [
                        'items' => [],
                        'tong_tien' => 0,
                        'tong_so_luong' => 0
                    ]
                ]);
            }

            $chiTietGioHang = $gioHang->cartItem()
                ->with([
                    'variant' => function ($q) {
                        $q->withTrashed()->with(['attributeValues.attribute', 'product' => function ($q2) {
                            $q2->withTrashed();
                        }]);
                    },
                    'product' => function ($q) {
                        $q->withTrashed();
                    }
                ])
                ->get();

            $issueMessages = [];
            $adjustedItemIds = [];

            DB::beginTransaction();
            try {
                foreach ($chiTietGioHang as $item) {
                    $product = $item->product;
                    $variant = $item->variant;

                    if (!$product || $product->trashed()) {
                        $issueMessages[] = ($item->product?->ten ?? 'Sản phẩm không xác định') . ' - đã bị xóa khỏi hệ thống.';
                        $item->delete();
                        continue;
                    }

                    if ($variant) {
                        if ($variant->trashed()) {
                            $issueMessages[] = $product->ten . ' - biến thể đã bị xóa.';
                            $item->delete();
                            continue;
                        }
                        $availableQty = max((int) $variant->so_luong, 0);
                    } else {
                        $availableQty = max((int) $product->so_luong, 0);
                    }

                    if ($availableQty <= 0) {
                        $issueMessages[] = $product->ten . ' - đã hết hàng.';
                        $item->delete();
                        continue;
                    }

                    if ($item->so_luong > $availableQty) {
                        $item->so_luong = $availableQty;
                        $item->updateThanhTien();
                        $item->save();
                        $adjustedItemIds[$item->id] = $availableQty;
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            // Reload items after adjustments/removals
            $chiTietGioHang = $gioHang->cartItem()
                ->with([
                    'variant' => function ($q) {
                        $q->withTrashed()->with(['attributeValues.attribute', 'product' => function ($q2) {
                            $q2->withTrashed();
                        }]);
                    },
                    'product' => function ($q) {
                        $q->withTrashed();
                    }
                ])
                ->get();

            $items = $chiTietGioHang->map(function ($item) use ($adjustedItemIds) {
                $product = $item->product;
                $variant = $item->variant;

                $error = null;
                if (isset($adjustedItemIds[$item->id])) {
                    $error = 'Số lượng đã được điều chỉnh theo tồn kho. Tối đa hiện có: ' . $adjustedItemIds[$item->id] . '.';
                }

                return [
                    'id' => $item->id,
                    'san_pham_id' => $item->san_pham_id,
                    'ten_san_pham' => $product?->ten,
                    'hinh_anh' => $product?->hinh_anh,
                    'so_luong' => $item->so_luong,
                    'gia_san_pham' => $item->gia_san_pham,
                    'thanh_tien' => $item->thanh_tien,
                    'bien_the' => $variant ? [
                        'id' => $variant->id,
                        'thuoc_tinh' => $variant->attributeValues->map(function ($attrValue) {
                            return [
                                'ten_thuoc_tinh' => $attrValue->attribute->ten,
                                'gia_tri' => $attrValue->gia_tri
                            ];
                        })
                    ] : null,
                    'error_message' => $error
                ];
            });

            if (!empty($issueMessages)) {
                Mail::to($user->email)->queue(new CartItemIssueMail($issueMessages));
            }


            $tongTien = $gioHang->cartItem()->sum('thanh_tien');
            $tongSoLuong = $gioHang->cartItem()->sum('so_luong');

            return response()->json([
                'message' => 'Lấy giỏ hàng thành công',
                'data' => [
                    'items' => $items,
                    'tong_tien' => $tongTien,
                    'tong_so_luong' => $tongSoLuong
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi xem giỏ hàng', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return response()->json(['error' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }



    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'san_pham_id' => 'required|exists:san_phams,id',
            'so_luong' => 'required|integer|min:1',
            'bien_the_id' => 'nullable|exists:bien_thes,id',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $gioHang = $user->cart;
            if (!$gioHang) {
                $gioHang = Cart::create(['user_id' => $user->id]);
            }
            $existingItem = CartItem::where('gio_hang_id', $gioHang->id)
                ->where('san_pham_id', $validated['san_pham_id'])
                ->where('bien_the_id', $validated['bien_the_id'])
                ->first();

            if ($existingItem) {
                $soLuongTong = $existingItem->so_luong + $validated['so_luong'];

                // Kiểm tra tồn kho
                $soLuongTonKho = 0;
                if ($validated['bien_the_id']) {
                    $bienThe = Variant::findOrFail($validated['bien_the_id']);
                    $soLuongTonKho = $bienThe->so_luong;
                } else {
                    $sanPham = Product::findOrFail($validated['san_pham_id']);
                    $soLuongTonKho = $sanPham->so_luong;
                }

                if ($soLuongTong > $soLuongTonKho) {
                    return response()->json([
                        'error' => 'Số lượng vượt quá tồn kho. Chỉ còn ' . $soLuongTonKho . ' sản phẩm.'
                    ], 400);
                }

                $existingItem->so_luong = $soLuongTong;
                $existingItem->updateThanhTien();
                $existingItem->save();
            } else {
                // Tạo mới cart item - kiểm tra tồn kho trước
                $giaSanPham = 0;
                $soLuongTonKho = 0;

                if ($validated['bien_the_id']) {
                    $bienThe = Variant::findOrFail($validated['bien_the_id']);
                    $giaSanPham = $bienThe->gia_khuyen_mai ?? $bienThe->gia;
                    $soLuongTonKho = $bienThe->so_luong;
                } else {
                    $sanPham = Product::findOrFail($validated['san_pham_id']);
                    $giaSanPham = $sanPham->gia_khuyen_mai ?? $sanPham->gia;
                    $soLuongTonKho = $sanPham->so_luong;
                }

                if ($validated['so_luong'] > $soLuongTonKho) {
                    return response()->json([
                        'error' => 'Số lượng vượt quá tồn kho. Chỉ còn ' . $soLuongTonKho . ' sản phẩm.'
                    ], 400);
                }

                CartItem::create([
                    'gio_hang_id' => $gioHang->id,
                    'san_pham_id' => $validated['san_pham_id'],
                    'bien_the_id' => $validated['bien_the_id'],
                    'so_luong' => $validated['so_luong'],
                    'gia_san_pham' => $giaSanPham,
                    'thanh_tien' => $giaSanPham * $validated['so_luong'],
                ]);
            }


            DB::commit();

            return response()->json([
                'message' => 'Thêm vào giỏ hàng thành công!'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi thêm vào giỏ hàng', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'data' => $validated
            ]);
            return response()->json(['error' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function updateQuantity(Request $request, $id)
    {
        $validated = $request->validate([
            'so_luong' => 'required|integer|min:1',
            'action' => 'nullable|in:replace,add',
        ]);

        try {
            $user = Auth::user();
            $chiTietGioHang = CartItem::where('id', $id)
                ->whereHas('cart', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->firstOrFail();

            $action = $validated['action'] ?? 'replace';
            if ($chiTietGioHang->bien_the_id) {
                $bienThe = Variant::withTrashed()->find($chiTietGioHang->bien_the_id);
                $soLuongTonKho = $bienThe?->so_luong ?? 0;
            } else {
                $sanPham = Product::withTrashed()->find($chiTietGioHang->san_pham_id);
                $soLuongTonKho = $sanPham?->so_luong ?? 0;
            }
            $soLuongMoi = $action === 'add'
                ? $chiTietGioHang->so_luong + $validated['so_luong']
                : $validated['so_luong'];
            if ($soLuongMoi > $soLuongTonKho) {
                return response()->json([
                    'error' => 'Số lượng vượt quá tồn kho. Chỉ còn ' . $soLuongTonKho . ' sản phẩm.'
                ], 400);
            }

            $chiTietGioHang->so_luong = $soLuongMoi;
            $chiTietGioHang->updateThanhTien();
            $chiTietGioHang->save();

            return response()->json([
                'message' => 'Cập nhật số lượng thành công!',
                'data' => [
                    'so_luong' => $chiTietGioHang->so_luong,
                    'thanh_tien' => $chiTietGioHang->thanh_tien,
                    'action' => $action
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật số lượng', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'chi_tiet_id' => $id
            ]);
            return response()->json(['error' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }


    public function removeItem($id)
    {
        try {
            $user = Auth::user();
            $chiTietGioHang = CartItem::where('id', $id)
                ->whereHas('cart', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->firstOrFail();

            $chiTietGioHang->delete();

            return response()->json([
                'message' => 'Xóa sản phẩm khỏi giỏ hàng thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa sản phẩm khỏi giỏ hàng', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'chi_tiet_id' => $id
            ]);
            return response()->json(['error' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function clearCart()
    {
        try {
            $user = Auth::user();
            $gioHang = $user->cart;

            if ($gioHang) {
                $gioHang->cartItem()->delete();
            }

            return response()->json([
                'message' => 'Đã xóa tất cả sản phẩm trong giỏ hàng!'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa tất cả giỏ hàng', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return response()->json(['error' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}
