<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingFee extends Model
{
    use HasFactory;
    protected $table = 'phi_ships';

    protected $fillable = [
        'tinh_thanh',
        'phi',
    ];
}
