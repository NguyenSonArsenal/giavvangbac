<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SilverPriceHistory extends Model
{
    protected $table = 'silver_price_history';

    protected $fillable = [
        'source',
        'unit',
        'buy_price',
        'sell_price',
        'price_date',
        'recorded_at',
    ];

    protected $casts = [
        'buy_price'   => 'integer',
        'sell_price'  => 'integer',
        'price_date'  => 'date',
        'recorded_at' => 'datetime',
    ];

    /**
     * Lấy lịch sử N ngày theo nguồn và đơn vị, gom nhóm theo ngày (lấy bản ghi cuối mỗi ngày)
     */
    public static function getHistory(string $source, string $unit, int $days): \Illuminate\Support\Collection
    {
        $from = now()->subDays($days)->startOfDay();

        return static::where('source', $source)
            ->where('unit', $unit)
            ->where('recorded_at', '>=', $from)
            ->orderBy('price_date')
            ->orderBy('recorded_at')
            ->get()
            ->groupBy(function ($r) {
                return $r->price_date->format('Y-m-d');
            })
            ->map(function ($group) {
                return $group->last(); // 1 điểm / ngày (bản ghi cuối)
            });
    }
}
