<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'don_hangs';

    protected $fillable = [
        'ma_don_hang',
        'user_id',
        'phuong_thuc_thanh_toan_id',
        'trang_thai_don_hang',
        'trang_thai_thanh_toan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }



   
    public function orderDetail()
    {
        return $this->hasMany(OrderDetail::class, 'don_hang_id');
    }

    
}
