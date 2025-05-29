<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tieu_de',
        'hinh_anh',
        'link',
        'trang_thai',
        'thu_tu'
    ];
}

