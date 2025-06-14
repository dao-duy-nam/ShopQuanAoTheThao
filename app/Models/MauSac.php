<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MauSac extends Model
{
    //
     protected $fillable = ['ten_mau_sac'];

    public function bienThes()
    {
        return $this->hasMany(BienThe::class);
    }
}
