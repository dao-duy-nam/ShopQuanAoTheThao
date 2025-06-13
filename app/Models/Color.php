<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Color extends Model
{
    use HasFactory;

    protected $table = 'mau_sacs';

    protected $fillable = [
        'ten_mau_sac',
    ];

    // Relationships
    public function Variant()
    {
        return $this->hasMany(Variant::class, 'mau_sac_id');
    }
}
