<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BienThe extends Model
{
    protected $table = 'bien_thes';

        protected $fillable = [
        'san_pham_id',
        'so_luong',
        'gia',
        'gia_khuyen_mai',
        'hinh_anh',
        'so_luong_da_ban',
    ];
    
    public function sanphams()
    {
        return $this->belongsTo(Product::class);
    }

    public function kichCo()
    {
        return $this->belongsTo(KichCo::class);
    }

    public function mauSac()
    {
        return $this->belongsTo(MauSac::class);
    }

}
