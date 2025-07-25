<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
     use HasFactory;

    protected $table = 'contact';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'type',
        'status',
        'replied_at',
        'attachment',
        'reply_content'

    ];

    protected $casts = [
        'replied_at' => 'datetime',
        
    ];

}
