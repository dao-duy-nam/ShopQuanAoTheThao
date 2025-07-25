<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'san_phams'; 

    protected $fillable = [
        'ten',
        'so_luong',
        'so_luong_da_ban',
        'mo_ta',
        'hinh_anh',
        'danh_muc_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'danh_muc_id');
    }
    public function danhGias()
    {
        return $this->hasMany(DanhGia::class);
    }

    public function discountCodes()
    {
        return $this->hasMany(DiscountCode::class, 'san_pham_id');
    }
    public function variants()
    {
        return $this->hasMany(Variant::class, 'san_pham_id');
    }
    
}
