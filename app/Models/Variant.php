<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variant extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'bien_thes';

    protected $fillable = [
        'san_pham_id',
        'kich_co_id',
        'mau_sac_id',
        'so_luong',
        'so_luong_da_ban',
        'gia',
        'gia_khuyen_mai',
        'hinh_anh'
    ];
    protected $casts = ['hinh_anh' => 'array'];
   
    public function Product()
    {
        return $this->belongsTo(Product::class, 'san_pham_id');
    }

  
    public function Size()
    {
        return $this->belongsTo(Size::class, 'kich_co_id');
    }

   
    public function Color()
    {
        return $this->belongsTo(Color::class, 'mau_sac_id');
    }
}
