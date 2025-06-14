<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KichCo extends Model
{
    protected $fillable = ['kich_co'];

    public function bienThes()
    {
        return $this->hasMany(BienThe::class);
    }
}
