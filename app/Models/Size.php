<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Size extends Model
{
    use HasFactory;

    protected $table = 'kich_cos';

    protected $fillable = [
        'kich_co',
    ];

    
    public function Variant()
    {
        return $this->hasMany(Variant::class, 'kich_co_id');
    }
}
