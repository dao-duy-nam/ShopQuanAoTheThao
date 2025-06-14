<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BienThe extends Model
{
    protected $table = 'bien_thes';

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
