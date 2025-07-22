<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'bai_viet';
    protected $dates = ['deleted_at']; 

    protected $fillable = [
        'tieu_de',
        'mo_ta_ngan',
        'noi_dung',
        'anh_dai_dien',
        'trang_thai',
    ];
}
