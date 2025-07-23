<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Resources\ShippingFeeResource;
use App\Models\ShippingFee;
use Illuminate\Http\Request;

class ShippingFeeController 
{
    public function index()
    {
        return ShippingFeeResource::collection(ShippingFee::all());
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
