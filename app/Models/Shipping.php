<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
     protected $table = 'phi_ships';
    protected $fillable = [
        'tinh_thanh',
        'phi',
    ];
}
