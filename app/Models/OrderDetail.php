<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'chi_tiet_don_hangs'; 

    protected $fillable = [
        'don_hang_id',    
        'san_pham_id',    
        'bien_the_id',    
        'so_luong',       
        'don_gia',        
        'tong_tien',      
        'thuoc_tinh_bien_the',  
        'ma_giam_gia_id',
        'ma_giam_gia',   
        'so_tien_duoc_giam', 
    ];

    
    public function order()
    {
        return $this->belongsTo(Order::class, 'don_hang_id');
    }

    
    public function product()
    {
        return $this->belongsTo(Product::class, 'san_pham_id');
    }

    
    public function variant()
    {
        return $this->belongsTo(Variant::class, 'bien_the_id');
    }
}
