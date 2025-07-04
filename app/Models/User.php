<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'so_dien_thoai',
        'ngay_sinh',
        'gioi_tinh',
        'anh_dai_dien',
        'vai_tro_id',
        'trang_thai',
        'ly_do_block'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',

        'otp_attempts' => 'integer',
        'otp_locked_until' => 'datetime',
        'otp_expired_at' => 'datetime',
    ];
    public function danhGias()
{
    return $this->hasMany(DanhGia::class);
}



   public function role()
{
    return $this->belongsTo(Role::class, 'vai_tro_id', 'id');
}


    const ROLE_ADMIN = 1;
    const ROLE_USER = 2;
    const ROLE_STAFF = 3;


    protected $attributes = [
        'vai_tro_id' => self::ROLE_USER,
    ];

    public function isRoleAdmin()
    {
        return $this->vai_tro_id == self::ROLE_ADMIN;
    }

    public function isRoleUser()
    {
        return $this->vai_tro_id == self::ROLE_USER;
    }

    public function isRoleStaff()
    {
        return $this->vai_tro_id == self::ROLE_STAFF;
    }


    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    
    public function reviews()
    {
        return $this->hasMany(DanhGia::class, 'user_id');
    }
     public function cart()
    {
        return $this->hasOne(Cart::class, 'user_id');
    }
}

