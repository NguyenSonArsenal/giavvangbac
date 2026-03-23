<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SilverTrendLog extends Model
{
    protected $fillable = [
        'analysis',
        'source',
        'pct_change',
        'trend',
        'high_price',
        'low_price',
        'latest_price',
        'raw_stats',
        'is_accurate',
        'admin_note',
    ];

    protected $casts = [
        'pct_change'   => 'decimal:2',
        'high_price'   => 'integer',
        'low_price'    => 'integer',
        'latest_price' => 'integer',
        'raw_stats'    => 'array',
        'is_accurate'  => 'boolean',
    ];

    /**
     * Lấy nhận định mới nhất
     */
    public static function latest(): ?self
    {
        return static::orderByDesc('created_at')->first();
    }
}
