<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'interviewer_name',
        'interviewee_name',
        'interviewee_label',
        'scheduled_at',
        'started_at',
        'ended_at',
        'description',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class)
            ->orderByRaw('COALESCE(position, 999999)')
            ->orderBy('spoken_at')
            ->orderBy('id');
    }
}

