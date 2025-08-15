<?php

namespace App\Models;

use App\Models\User;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Model;

class DanhGia extends Model
{
    protected $table = 'danh_gias';
    protected $fillable = [
        'user_id',
        'chi_tiet_don_hang_id',
        'san_pham_id',
        'bien_the_id',
        'noi_dung',
        'so_sao',
        'hinh_anh',
        'is_hidden'
    ];
    protected $casts = [
        'hinh_anh' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'san_pham_id');
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class, 'bien_the_id');
    }
    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class, 'chi_tiet_don_hang_id');
    }
}
