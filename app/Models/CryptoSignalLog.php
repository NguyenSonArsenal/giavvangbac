<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoSignalLog extends Model
{
    protected $table = 'crypto_signal_logs';

    protected $fillable = [
        'symbol',
        'interval',
        'price',
        'ma7',
        'ma25',
        'ma99',
        'rsi',
        'volume_current',
        'volume_avg',
        'score',
        'signal_type',
        'reasons',
        'warnings',
        'scanned_at',
    ];

    protected $casts = [
        'price'          => 'float',
        'ma7'            => 'float',
        'ma25'           => 'float',
        'ma99'           => 'float',
        'rsi'            => 'float',
        'volume_current' => 'float',
        'volume_avg'     => 'float',
        'score'          => 'integer',
        'reasons'        => 'array',
        'warnings'       => 'array',
        'scanned_at'     => 'datetime',
    ];

    // ── Scope: chỉ lấy tín hiệu đáng xem (score >= 3) ──────────────────────
    public function scopeWorthy($query)
    {
        return $query->where('score', '>=', 3);
    }

    // ── Scope: tín hiệu mua mạnh ────────────────────────────────────────────
    public function scopeStrongBuy($query)
    {
        return $query->where('signal_type', 'STRONG_BUY');
    }

    // ── Lấy tín hiệu gần nhất của 1 symbol ──────────────────────────────────
    public static function latestFor(string $symbol): ?self
    {
        return static::where('symbol', $symbol)
            ->orderByDesc('scanned_at')
            ->first();
    }
}
