<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_gio_hangs';

    protected $fillable = [
        'gio_hang_id',
        'san_pham_id',
        'bien_the_id',
        'so_luong',
        'gia_san_pham',
        'thanh_tien',
    ];

    protected $casts = [
        'gia_san_pham' => 'decimal:2',
        'thanh_tien' => 'decimal:2',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'gio_hang_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'san_pham_id');
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class, 'bien_the_id');
    }

    public function getGiaHienTaiAttribute()
    {
        if ($this->bien_the_id) {
            return $this->variant->gia_khuyen_mai ?? $this->variant->gia;
        }
        return $this->product->gia_khuyen_mai ?? $this->product->gia;
    }

    public function updateThanhTien()
    {
        $this->thanh_tien = $this->so_luong * $this->gia_san_pham;
        $this->save();
    }
} 