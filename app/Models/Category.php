<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'danh_mucs';

    protected $fillable = [
        'ten',
        'mo_ta',
    ];

    protected $dates = ['deleted_at']; 

    public function products()
    {
        return $this->hasMany(Product::class, 'danh_muc_id');
    }
}
