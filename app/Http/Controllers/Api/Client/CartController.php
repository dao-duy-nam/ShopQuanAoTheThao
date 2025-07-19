<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                ->with(['product', 'variant.attributeValues.attribute'])
                ->get();

            $items = $chiTietGioHang->map(function ($item) {
                $data = [
                    'id' => $item->id,
                    'san_pham_id' => $item->san_pham_id,
                    'ten_san_pham' => $item->product->ten,
                    'hinh_anh' => $item->product->hinh_anh,
                    'so_luong' => $item->so_luong,
                    'gia_san_pham' => $item->gia_san_pham,
                    'thanh_tien' => $item->thanh_tien,
                    'bien_the' => null
                ];

                if ($item->bien_the_id) {
                    $data['bien_the'] = [
                        'id' => $item->variant->id,
                        'thuoc_tinh' => $item->variant->attributeValues->map(function ($attrValue) {
                            return [
                                'ten_thuoc_tinh' => $attrValue->attribute->ten,
                                'gia_tri' => $attrValue->gia_tri
                            ];
                        })
                    ];
                }

                return $data;
            });

            return response()->json([
                'message' => 'Lấy giỏ hàng thành công',
                'data' => [
                    'items' => $items,
                    'tong_tien' => $gioHang->tong_tien,
                    'tong_so_luong' => $gioHang->tong_so_luong
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

            $tongSoLuongMuonThem = $validated['so_luong'];
            if ($existingItem) {
                $tongSoLuongMuonThem += $existingItem->so_luong;
            }

            // Kiểm tra tồn kho
            if ($validated['bien_the_id']) {
                $bienThe = Variant::findOrFail($validated['bien_the_id']);
                if ($tongSoLuongMuonThem > $bienThe->so_luong) {
                    return response()->json([
                        'error' => 'Số lượng vượt quá tồn kho của biến thể! Chỉ còn ' . $bienThe->so_luong
                    ], 422);
                }
                $giaSanPham = $bienThe->gia_khuyen_mai ?? $bienThe->gia;
            } else {
                $sanPham = Product::findOrFail($validated['san_pham_id']);
                if ($tongSoLuongMuonThem > $sanPham->so_luong) {
                    return response()->json([
                        'error' => 'Số lượng vượt quá tồn kho của sản phẩm! Chỉ còn ' . $sanPham->so_luong
                    ], 422);
                }
                $giaSanPham = $sanPham->gia_khuyen_mai ?? $sanPham->gia;
            }

            if ($existingItem) {
                $existingItem->so_luong = $tongSoLuongMuonThem;
                $existingItem->updateThanhTien();
                $existingItem->save();
            } else {
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
            $soLuongMoi = $validated['so_luong'];
            if ($action === 'add') {
                $soLuongMoi += $chiTietGioHang->so_luong;
            }

            // Kiểm tra tồn kho
            if ($chiTietGioHang->bien_the_id) {
                $bienThe = Variant::findOrFail($chiTietGioHang->bien_the_id);
                if ($soLuongMoi > $bienThe->so_luong) {
                    return response()->json([
                        'error' => 'Số lượng vượt quá tồn kho của biến thể! Chỉ còn ' . $bienThe->so_luong
                    ], 422);
                }
            } else {
                $sanPham = Product::findOrFail($chiTietGioHang->san_pham_id);
                if ($soLuongMoi > $sanPham->so_luong) {
                    return response()->json([
                        'error' => 'Số lượng vượt quá tồn kho của sản phẩm! Chỉ còn ' . $sanPham->so_luong
                    ], 422);
                }
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