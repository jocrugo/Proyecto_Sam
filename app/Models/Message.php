<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_id',
        'sender_role',
        'content',
        'position',
        'spoken_at',
    ];

    protected $casts = [
        'spoken_at' => 'datetime',
    ];
}

