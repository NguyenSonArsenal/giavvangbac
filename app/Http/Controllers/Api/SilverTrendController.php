<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SilverPriceHistory;
use App\Models\SilverTrendLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SilverTrendController extends Controller
{
    /**
     * GET /api/silver/trend
     * Trả về nhận định xu hướng giá bạc.
     * Ưu tiên: Cache → DB → Generate mới
     */
    public function trend(): JsonResponse
    {
        // 1. Kiểm tra cache
        $cached = Cache::get('silver_trend_analysis');
        if ($cached) {
            return response()->json([
                'success' => true,
                'data'    => $cached,
                'cached'  => true,
            ]);
        }

        // 2. Lấy từ DB (bản ghi mới nhất)
        $latest = SilverTrendLog::orderByDesc('created_at')->first();

        if ($latest) {
            $result = [
                'analysis'   => $latest->analysis,
                'stats'      => $latest->raw_stats,
                'updated_at' => $latest->created_at->format('H:i d/m/Y'),
                'log_id'     => $latest->id,
            ];

            // Cache lại 12 giờ
            Cache::put('silver_trend_analysis', $result, now()->addHours(12));

            return response()->json([
                'success' => true,
                'data'    => $result,
                'cached'  => false,
            ]);
        }

        // 3. Chưa có dữ liệu → chạy command generate ngay
        try {
            Artisan::call('silver:generate-trend');

            $newLog = SilverTrendLog::orderByDesc('created_at')->first();
            if ($newLog) {
                $result = [
                    'analysis'   => $newLog->analysis,
                    'stats'      => $newLog->raw_stats,
                    'updated_at' => $newLog->created_at->format('H:i d/m/Y'),
                    'log_id'     => $newLog->id,
                ];

                return response()->json([
                    'success' => true,
                    'data'    => $result,
                    'cached'  => false,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('[SilverTrend] Auto-generate failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => false,
            'message' => 'Chưa có dữ liệu nhận định. Vui lòng chờ hệ thống phân tích.',
            'data'    => null,
        ]);
    }
}
