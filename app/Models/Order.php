<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'don_hangs';

    protected $casts = [
    'thoi_gian_nhan' => 'datetime',
    ];

    protected $fillable = [
        'ma_don_hang',
        'user_id',
        'dia_chi',
        'dia_chi_day_du',
        'phuong_thuc_thanh_toan_id',
        'trang_thai_don_hang',
        'trang_thai_thanh_toan',
        'so_tien_thanh_toan',
        'ten_san_pham',
        'gia_tri_bien_the',
        'thanh_pho',
        'huyen',
        'xa',
        'email_nguoi_dat',
        'ten_nguoi_dat',
        'sdt_nguoi_dat',
        'ghi_chu_admin',       
        'expires_at',          
        'payment_link',
        'phi_ship',
        'ma_giam_gia_id',
        'ma_giam_gia',
        'so_tien_duoc_giam', 
        'ly_do_huy', 
        'tu_choi_tra_hang', 
        'ly_do_tu_choi_tra_hang', 
        'xac_nhan_da_giao',
        'ngay_thanh_toan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }




    public function orderDetail()
    {
        return $this->hasMany(OrderDetail::class, 'don_hang_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'phuong_thuc_thanh_toan_id');
    }
}
