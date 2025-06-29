<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'gio_hangs';

    protected $fillable = [
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cartItem()
    {
        return $this->hasMany(cartItem::class, 'gio_hang_id');
    }

    public function getTongTienAttribute()
    {
        return $this->cartItem->sum('thanh_tien');
    }

    public function getTongSoLuongAttribute()
    {
        return $this->cartItem->sum('so_luong');
    }
} 