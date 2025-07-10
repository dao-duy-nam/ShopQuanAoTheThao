<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiscountCode extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'ma_giam_gias';

    protected $fillable = [
        'ma',
        'ten',
        'loai',
        'ap_dung_cho',
        'san_pham_id',
        'gia_tri',
        'gia_tri_don_hang',
        'so_luong',
        'so_lan_su_dung',
        'gioi_han',
        'ngay_bat_dau',
        'ngay_ket_thuc',
        'trang_thai',
    ];

    protected $casts = [
        'ngay_bat_dau' => 'datetime',
        'ngay_ket_thuc' => 'datetime',
        'trang_thai' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'san_pham_id');
    }
}
