<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;
    protected $table = 'vai_tros';

    protected $fillable = [
        'ten_vai_tro',
        'mo_ta',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
