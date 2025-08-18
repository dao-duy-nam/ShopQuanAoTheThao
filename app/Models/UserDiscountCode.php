<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDiscountCode extends Model
{
    // Giữ nguyên tên bảng tiếng Việt
    protected $table = 'ma_giam_gia_nguoi_dung';

    // Cho phép fill các trường tiếng Việt
    protected $fillable = [
        'ma_giam_gia_id',
        'nguoi_dung_id',
        'so_lan_da_dung',
    ];

    // Quan hệ với bảng users
    public function user()
    {
        return $this->belongsTo(User::class, 'nguoi_dung_id');
    }

    // Quan hệ với bảng mã giảm giá
    public function discountCode()
    {
        return $this->belongsTo(DiscountCode::class, 'ma_giam_gia_id');
    }
}
