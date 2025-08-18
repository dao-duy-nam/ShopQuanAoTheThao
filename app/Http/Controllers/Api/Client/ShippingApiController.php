<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Shipping;
use Illuminate\Http\Request;

class ShippingApiController extends Controller
{
    public function getPhiShip(Request $request)
    {
        $tinh_thanh = $request->query('tinh_thanh');

        if (!$tinh_thanh) {
            return response()->json([
                'message' => 'Thiếu thông tin tỉnh/thành.',
            ], 400);
        }

        $shipping = Shipping::where('tinh_thanh', $tinh_thanh)->first();

        if (!$shipping) {
            return response()->json([
                'phi' => 0,
                'message' => 'Không tìm thấy phí ship cho tỉnh/thành này.',
            ], 404);
        }

        return response()->json([
            'phi' => $shipping->phi,
            'tinh_thanh' => $shipping->tinh_thanh,
        ]);
    }
}
