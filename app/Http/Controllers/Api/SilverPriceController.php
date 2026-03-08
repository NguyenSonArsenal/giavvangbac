<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SilverPrice;
use App\Models\SilverPriceHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SilverPriceController extends Controller
{
    /**
     * GET /api/silver/current
     * Trả về giá hiện tại của tất cả đơn vị từ nguồn phuquy
     */
    public function currentPrice(): JsonResponse
    {
        $prices = SilverPrice::allBySource('phuquy');

        if ($prices->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa có dữ liệu. Vui lòng chạy: php artisan silver:fetch-phuquy',
                'data'    => null,
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

        // LUONG = CHI × 10 (không lưu DB riêng, tính runtime)
        if (!isset($byUnit['LUONG']) && isset($byUnit['CHI'])) {
            $chi = $byUnit['CHI'];
            $byUnit['LUONG'] = [
                'unit'           => 'LUONG',
                'product_name'   => 'Bạc 999 Phú Quý (Lượng)',
                'buy_price'      => $chi['buy_price']  * 10,
                'sell_price'     => $chi['sell_price'] * 10,
                'buy_formatted'  => number_format($chi['buy_price']  * 10),
                'sell_formatted' => number_format($chi['sell_price'] * 10),
                'recorded_at'    => $chi['recorded_at'],
            ];
        }

        return response()->json([
            'success' => true,
            'source'  => 'phuquy',
            'data'    => $byUnit,
        ]);
    }

    /**
     * GET /api/silver/history?days=7&type=KG
     * Trả về mảng ngày + giá mua/bán để vẽ chart
     */
    public function history(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 7);
        $unit = strtoupper($request->get('type', 'KG'));

        $days = max(1, min(365, $days));
        $unit = in_array($unit, ['KG', 'CHI', 'LUONG']) ? $unit : 'KG';

        // LUONG = CHI × 10 (Phú Quý chart API không có endpoint riêng cho Lượng)
        $fetchUnit  = ($unit === 'LUONG') ? 'CHI' : $unit;
        $multiplier = ($unit === 'LUONG') ? 10 : 1;

        $history = SilverPriceHistory::getHistory('phuquy', $fetchUnit, $days);

        if ($history->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa có dữ liệu lịch sử.',
                'data'    => ['dates' => [], 'buy_prices' => [], 'sell_prices' => []],
            ]);
        }

        $dates      = [];
        $buyPrices  = [];
        $sellPrices = [];

        foreach ($history as $record) {
            $dates[]      = $record->price_date->format('d/m');
            $buyPrices[]  = $record->buy_price  * $multiplier;
            $sellPrices[] = $record->sell_price * $multiplier;
        }

        return response()->json([
            'success'    => true,
            'unit'       => $unit,
            'days'       => $days,
            'type_label' => $this->unitLabel($unit),
            'data'       => [
                'dates'       => $dates,
                'buy_prices'  => $buyPrices,
                'sell_prices' => $sellPrices,
            ],
        ]);
    }

    /**
     * GET /api/silver/percent?days=7
     * Trả về % thay đổi giá trong N ngày
     */
    public function percent(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 7);
        $days = max(1, min(365, $days));
        $unit = 'KG';

        $from = now()->subDays($days)->startOfDay();

        $oldest = SilverPriceHistory::where('source', 'phuquy')
            ->where('unit', $unit)
            ->where('recorded_at', '>=', $from)
            ->orderBy('recorded_at')
            ->first();

        $latest = SilverPriceHistory::where('source', 'phuquy')
            ->where('unit', $unit)
            ->orderByDesc('recorded_at')
            ->first();

        if (!$oldest || !$latest) {
            return response()->json([
                'success'  => true,
                'percent'  => null,
                'trend'    => 'neutral',
                'days'     => $days,
                'message'  => 'Chưa đủ dữ liệu để tính %',
            ]);
        }

        $oldSell = $oldest->sell_price;
        $newSell = $latest->sell_price;

        $pct = $oldSell > 0
            ? round((($newSell - $oldSell) / $oldSell) * 100, 2)
            : 0;

        return response()->json([
            'success'    => true,
            'percent'    => abs($pct),
            'percent_raw'=> $pct,
            'trend'      => $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'neutral'),
            'days'       => $days,
            'updated_at' => $latest->recorded_at ? $latest->recorded_at->format('H:i d/m/Y') : null,
        ]);
    }

    private function unitLabel(string $unit): string
    {
        if ($unit === 'CHI') {
            return 'VND/Chỉ';
        } elseif ($unit === 'LUONG') {
            return 'VND/Lượng';
        } elseif ($unit === 'KG') {
            return 'VND/Kilogram';
        }
        return $unit;
    }
}
