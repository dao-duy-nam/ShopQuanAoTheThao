<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'san_phams'; // Khai báo lại table gốc

    protected $fillable = [
        'ten',
        'gia',
        'gia_khuyen_mai',
        'so_luong',
        'mo_ta',
        'hinh_anh',
        'danh_muc_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'danh_muc_id');
    }
}

