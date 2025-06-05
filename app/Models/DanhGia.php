<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanhGia extends Model
{
    //
     protected $fillable = ['user_id', 'san_pham_id',
     
        'noi_dung',
        'so_sao',
        'hinh_anh',
        'is_hidden'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sanphams()
    {
        return $this->belongsTo(Product::class);
    }
     public function bienThe()
    {
        return $this->belongsTo(BienThe::class);
    }
}
