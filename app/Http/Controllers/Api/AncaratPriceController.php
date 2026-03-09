<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SilverPriceHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AncaratPriceController extends Controller
{
    /**
     * GET /api/ancarat/current
     * Trả về giá hiện tại Ancarat: lấy record mới nhất mỗi unit từ silver_price_history
     */
    public function currentPrice(): JsonResponse
    {
        $units  = ['LUONG', 'KG'];
        $byUnit = [];

        foreach ($units as $unit) {
            $latest = SilverPriceHistory::where('source', 'ancarat')
                ->where('unit', $unit)
                ->orderByDesc('recorded_at')
                ->first();

            if (!$latest) {
                continue;
            }

            $byUnit[$unit] = [
                'unit'           => $unit,
                'buy_price'      => $latest->buy_price,
                'sell_price'     => $latest->sell_price,
                'buy_formatted'  => number_format($latest->buy_price),
                'sell_formatted' => number_format($latest->sell_price),
                'recorded_at'    => $latest->recorded_at ? $latest->recorded_at->format('d/m/Y H:i') : null,
            ];
        }

        if (empty($byUnit)) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa có dữ liệu. Chạy: php artisan silver:fetch-ancarat',
                'data'    => [],
            ], 404);
        }

        $latestAny = SilverPriceHistory::where('source', 'ancarat')
            ->orderByDesc('recorded_at')
            ->first();

        return response()->json([
            'success'    => true,
            'source'     => 'ancarat',
            'updated_at' => $latestAny && $latestAny->recorded_at
                ? $latestAny->recorded_at->format('H:i d/m/Y') : null,
            'data'       => $byUnit,
        ]);
    }

    /**
     * GET /api/ancarat/history?days=7&type=LUONG
     * type: LUONG | KG
     * days=1 → intraday (tất cả điểm trong ngày hôm nay, nhãn HH:MM)
     */
    public function history(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 7);
        $unit = strtoupper($request->get('type', 'LUONG'));

        $days = max(1, min(365, $days));
        $unit = in_array($unit, ['LUONG', 'KG']) ? $unit : 'LUONG';

        $labelMap = ['LUONG' => 'VND/Lượng', 'KG' => 'VND/Kilogram'];

        // ── 1D: intraday ──
        if ($days === 1) {
            $history = SilverPriceHistory::getIntradayHistory('ancarat', $unit);

            if ($history->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa có dữ liệu trong ngày hôm nay.',
                    'data'    => ['dates' => [], 'buy_prices' => [], 'sell_prices' => []],
                ]);
            }

            $dates = $buyPrices = $sellPrices = [];
            foreach ($history as $record) {
                $dates[]      = $record->recorded_at->format('H:i');
                $buyPrices[]  = $record->buy_price;
                $sellPrices[] = $record->sell_price;
            }

            return response()->json([
                'success'    => true,
                'unit'       => $unit,
                'days'       => 1,
                'type_label' => $labelMap[$unit] ?? $unit,
                'data'       => [
                    'dates'       => $dates,
                    'buy_prices'  => $buyPrices,
                    'sell_prices' => $sellPrices,
                ],
            ]);
        }

        // ── Multi-day ──
        $history = SilverPriceHistory::getHistory('ancarat', $unit, $days);

        if ($history->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa có dữ liệu lịch sử Ancarat.',
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
     * GET /api/ancarat/percent?days=7&type=LUONG
     */
    public function percent(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 7);
        $unit = strtoupper($request->get('type', 'LUONG'));

        $days = max(1, min(365, $days));
        $unit = in_array($unit, ['LUONG', 'KG']) ? $unit : 'LUONG';

        $from = now()->subDays($days)->startOfDay();

        $oldest = SilverPriceHistory::where('source', 'ancarat')
            ->where('unit', $unit)
            ->where('recorded_at', '>=', $from)
            ->orderBy('recorded_at')
            ->first();

        $latest = SilverPriceHistory::where('source', 'ancarat')
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
