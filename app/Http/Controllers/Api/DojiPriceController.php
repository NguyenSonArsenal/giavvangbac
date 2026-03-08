<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SilverPrice;
use App\Models\SilverPriceHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DojiPriceController extends Controller
{
    /**
     * GET /api/doji/current
     */
    public function currentPrice(): JsonResponse
    {
        $prices = SilverPrice::where('source', 'doji')->get();

        if ($prices->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa có dữ liệu. Chạy: php artisan silver:fetch-doji',
                'data'    => [],
            ], 404);
        }

        $byUnit = [];
        foreach ($prices as $p) {
            $byUnit[$p->unit] = [
                'unit'           => $p->unit,
                'product_name'   => $p->product_name,
                'buy_price'      => $p->buy_price,
                'sell_price'     => $p->sell_price,
                'buy_formatted'  => number_format($p->buy_price),
                'sell_formatted' => number_format($p->sell_price),
                'recorded_at'    => $p->recorded_at ? $p->recorded_at->format('d/m/Y H:i') : null,
            ];
        }

        $latest = SilverPrice::where('source', 'doji')->orderByDesc('recorded_at')->first();

        return response()->json([
            'success'    => true,
            'source'     => 'doji',
            'updated_at' => $latest && $latest->recorded_at
                ? $latest->recorded_at->format('H:i d/m/Y') : null,
            'data'       => $byUnit,
        ]);
    }

    /**
     * GET /api/doji/history?days=7&type=LUONG
     * type: LUONG | KG
     */
    public function history(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 7);
        $unit = strtoupper($request->get('type', 'LUONG'));

        $days = max(1, min(365, $days));
        $unit = in_array($unit, ['LUONG', 'KG']) ? $unit : 'LUONG';

        $history = SilverPriceHistory::getHistory('doji', $unit, $days);

        if ($history->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa có dữ liệu lịch sử DOJI.',
                'data'    => ['dates' => [], 'buy_prices' => [], 'sell_prices' => []],
            ]);
        }

        $dates      = [];
        $buyPrices  = [];
        $sellPrices = [];

        foreach ($history as $record) {
            $dates[]      = $record->price_date->format('d/m');
            $buyPrices[]  = $record->buy_price;
            $sellPrices[] = $record->sell_price;
        }

        $labelMap = ['LUONG' => 'VND/Lượng', 'KG' => 'VND/Kilogram'];

        return response()->json([
            'success'    => true,
            'unit'       => $unit,
            'days'       => $days,
            'type_label' => $labelMap[$unit] ?? $unit,
            'data'       => [
                'dates'       => $dates,
                'buy_prices'  => $buyPrices,
                'sell_prices' => $sellPrices,
            ],
        ]);
    }

    /**
     * GET /api/doji/percent?days=7&type=LUONG
     */
    public function percent(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 7);
        $unit = strtoupper($request->get('type', 'LUONG'));

        $days = max(1, min(365, $days));
        $unit = in_array($unit, ['LUONG', 'KG']) ? $unit : 'LUONG';

        $from = now()->subDays($days)->startOfDay();

        $oldest = SilverPriceHistory::where('source', 'doji')
            ->where('unit', $unit)
            ->where('recorded_at', '>=', $from)
            ->orderBy('recorded_at')
            ->first();

        $latest = SilverPriceHistory::where('source', 'doji')
            ->where('unit', $unit)
            ->orderByDesc('recorded_at')
            ->first();

        if (!$oldest || !$latest) {
            return response()->json([
                'success' => true,
                'percent' => null,
                'trend'   => 'neutral',
                'days'    => $days,
            ]);
        }

        $pct = $oldest->sell_price > 0
            ? round((($latest->sell_price - $oldest->sell_price) / $oldest->sell_price) * 100, 2)
            : 0;

        return response()->json([
            'success'     => true,
            'percent'     => abs($pct),
            'percent_raw' => $pct,
            'trend'       => $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'neutral'),
            'days'        => $days,
            'updated_at'  => $latest->recorded_at
                ? $latest->recorded_at->format('H:i d/m/Y') : null,
        ]);
    }
}
