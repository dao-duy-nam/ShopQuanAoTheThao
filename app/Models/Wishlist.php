<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $table = 'san_pham_yeu_thichs';

    protected $fillable = [
        'nguoi_dung_id',
        'san_pham_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'nguoi_dung_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'san_pham_id');
    }
}
