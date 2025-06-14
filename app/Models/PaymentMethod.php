<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    // Gắn với bảng phuong_thuc_thanh_toans
    protected $table = 'phuong_thuc_thanh_toans';

    // Cho phép gán các cột này
    protected $fillable = [
        'ten',
        'mo_ta',
        'trang_thai',
    ];

    // Một phương thức thanh toán có thể thuộc nhiều đơn hàng
    public function orders()
    {
        return $this->hasMany(Order::class, 'phuong_thuc_thanh_toan_id');
    }
}