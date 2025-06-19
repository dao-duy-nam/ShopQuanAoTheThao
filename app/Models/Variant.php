<?php

namespace App\Models;

use App\Models\Product;
use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variant extends Model
{
    use HasFactory, SoftDeletes;


    protected $table = 'bien_thes';

    protected $fillable = [
        'san_pham_id',
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

    public function attributeValues()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'bien_the_thuoc_tinhs',
            'bien_the_id',
            'gia_tri_thuoc_tinh_id'
        )->withTimestamps();
    }

    public function attributeMappings()
    {
        return $this->hasMany(VariantAttribute::class, 'bien_the_id');
    }
}
