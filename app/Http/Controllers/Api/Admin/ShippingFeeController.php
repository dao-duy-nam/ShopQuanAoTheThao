<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Resources\ShippingFeeResource;
use App\Models\ShippingFee;
use Illuminate\Http\Request;

class ShippingFeeController 
{
    public function index(Request $request)
    {
        $query = ShippingFee::query();

        if ($request->filled('search')) {
            $query->where('tinh_thanh', 'like', '%' . $request->search . '%');
        }

        return ShippingFeeResource::collection(
            $query->orderBy('gia_phi_ship', 'asc')->paginate(15)
        );
    }

    public function show($id)
    {
        $fee = ShippingFee::findOrFail($id);
        return new ShippingFeeResource($fee);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'phi' => 'sometimes|numeric|min:0',
        ]);

        $fee = ShippingFee::findOrFail($id);
        $fee->update($validated);

        return new ShippingFeeResource($fee);
    }
}
