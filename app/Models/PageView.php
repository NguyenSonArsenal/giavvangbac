<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    public $timestamps = false;

    protected $table = 'page_views';

    protected $fillable = [
        'url', 'ip', 'user_agent', 'session_id', 'referer', 'is_bot', 'created_at',
    ];

    protected $casts = [
        'is_bot'     => 'boolean',
        'created_at' => 'datetime',
    ];
}
