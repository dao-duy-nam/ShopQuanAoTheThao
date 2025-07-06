<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiaChi extends Model
{
    use HasFactory;

    protected $table = 'dia_chis';

    protected $fillable = [
        'user_id',
        'tinh_thanh',
        'quan_huyen',
        'phuong_xa',
        'dia_chi_chi_tiet',
        'mac_dinh',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 