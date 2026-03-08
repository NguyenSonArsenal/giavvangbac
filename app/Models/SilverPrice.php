<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SilverPrice extends Model
{
    protected $table = 'silver_prices';

    protected $fillable = [
        'source',
        'product_name',
        'unit',
        'buy_price',
        'sell_price',
        'recorded_at',
    ];

    protected $casts = [
        'buy_price'   => 'integer',
        'sell_price'  => 'integer',
        'recorded_at' => 'datetime',
    ];

    /**
     * Lấy giá mua/bán theo nguồn và đơn vị
     */
    public static function currentByUnit(string $source, string $unit): ?self
    {
        return static::where('source', $source)
                     ->where('unit', $unit)
                     ->first();
    }

    /**
     * Lấy tất cả giá hiện tại của một nguồn
     */
    public static function allBySource(string $source): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('source', $source)->get();
    }
}
