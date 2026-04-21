<?php

namespace App\Console\Commands;

use App\Models\SilverPriceHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchSilverAncarat extends Command
{
    protected $signature   = 'silver:fetch-ancarat';
    protected $description = 'Fetch giá bạc từ Ancarat (giabac.ancarat.com) mỗi 30 phút';

    const API_URL     = 'https://giabac.ancarat.com/api/price-data';
    const PAGE_URL    = 'https://giabac.ancarat.com/';

    // SKU đại diện chính cho mỗi unit – chỉ track A4 (Lượng) và K4 (Kilo)
    const PRIMARY_SKU = [
        'LUONG' => 'A4',  // Ngân Long Quảng Tiến 999 - 1 lượng
        'KG'    => 'K4',  // Ngân Long Quảng Tiến 999 - 1 Kilo
    ];

    public function handle(): int
    {
        $logFile = storage_path('logs/cron-silver-ancarat.log');
        $startAt = now()->format('Y-m-d H:i:s');

        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Fetch giá bạc Ancarat...');
        $inserted  = 0;
        $unchanged = 0;

        try {
            $res = Http::timeout(15)->get(self::API_URL);

            if (!$res->ok()) {
                $this->error('HTTP ' . $res->status());
                return Command::FAILURE;
            }

            $rows = $res->json();
            if (empty($rows)) {
                $this->error('API trả về rỗng');
                return Command::FAILURE;
            }

            // ── 1. Parse thời gian cập nhật ──────────────────────────────────
            // Giữ null nếu không parse được (để không ghi thời gian cron vào DB)
            $websiteTimestamp = null;

            // Kiểm tra trong rows: dòng đầu hoặc cuối có thể chứa datetime string
            foreach ($rows as $row) {
                if (!is_array($row)) continue;
                foreach ($row as $cell) {
                    if (!is_string($cell)) continue;
                    // Pattern: "DD/MM/YYYY HH:MM" hoặc "HH:MM DD/MM/YYYY"
                    if (preg_match('/(\d{2}\/\d{2}\/\d{4})\s+(\d{1,2}:\d{2})/', $cell, $m)) {
                        try { $websiteTimestamp = Carbon::createFromFormat('d/m/Y H:i', $m[1] . ' ' . $m[2]); } catch (\Exception) {}
                        break 2;
                    }
                    if (preg_match('/(\d{1,2}:\d{2})\s+(\d{2}\/\d{2}\/\d{4})/', $cell, $m)) {
                        try { $websiteTimestamp = Carbon::createFromFormat('H:i d/m/Y', $m[1] . ' ' . $m[2]); } catch (\Exception) {}
                        break 2;
                    }
                }
            }

            // Nếu không parse được từ API, fetch trang HTML để lấy thời gian
            if (!$websiteTimestamp) {
                try {
                    $pageRes = Http::timeout(15)
                        ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; GiaVangBot/1.0)'])
                        ->get(self::PAGE_URL);
                    if ($pageRes->ok()) {
                        $pageHtml = $pageRes->body();
                        if (preg_match('/(\d{2}\/\d{2}\/\d{4})\s+(\d{1,2}:\d{2})/', $pageHtml, $m)) {
                            try { $websiteTimestamp = Carbon::createFromFormat('d/m/Y H:i', $m[1] . ' ' . $m[2]); } catch (\Exception) {}
                        }
                        if (!$websiteTimestamp && preg_match('/(\d{1,2}:\d{2})\s+(\d{2}\/\d{2}\/\d{4})/', $pageHtml, $m)) {
                            try { $websiteTimestamp = Carbon::createFromFormat('H:i d/m/Y', $m[1] . ' ' . $m[2]); } catch (\Exception) {}
                        }
                    }
                } catch (\Exception) {}
            }

            if ($websiteTimestamp) {
                $this->info('  🕐 Thời gian website: ' . $websiteTimestamp->format('d/m/Y H:i'));
            } else {
                $this->warn('  ⚠ Không parse được thời gian website, sẽ giữ nguyên recorded_at cũ nếu giá không đổi');
            }

            // ── 2. Parse giá và lưu DB ──────────────────────────────────
            // Parse thành map: sku → [sell, buy]
            $priceMap = $this->parsePriceMap($rows);
            $this->info('  Parsed ' . count($priceMap) . ' sản phẩm có SKU');

            // Lưu history cho PRIMARY SKU (1 bảng duy nhất, dedup theo giá)
            foreach (self::PRIMARY_SKU as $unit => $sku) {
                if (!isset($priceMap[$sku])) {
                    $this->warn("  ⚠ SKU {$sku} không tìm thấy");
                    continue;
                }
                [$sell, $buy] = $priceMap[$sku];
                if ($buy <= 0 || $sell <= 0) {
                    $this->warn("  ⚠ SKU {$sku} giá = 0, bỏ qua");
                    continue;
                }

                // Dedup theo giá: lấy record cuối cùng, so sánh buy/sell
                $lastRecord = SilverPriceHistory::where('source', 'ancarat')
                    ->where('unit', $unit)
                    ->orderByDesc('recorded_at')
                    ->first();

                if ($lastRecord && (int)$lastRecord->buy_price === $buy && (int)$lastRecord->sell_price === $sell) {
                    if ($websiteTimestamp) {
                        // Website có timestamp thực → cập nhật recorded_at
                        $lastRecord->recorded_at = $websiteTimestamp;
                        $lastRecord->save();
                        $this->line("  🔄 [{$unit}/{$sku}] giá không đổi → cập nhật recorded_at = " . $websiteTimestamp->format('H:i d/m/Y'));
                    } else {
                        // Không có timestamp website → giữ nguyên, không ghi gì
                        $this->line("  ⏭  [{$unit}/{$sku}] giá không đổi, không có timestamp website → giữ nguyên");
                    }
                    $unchanged++;
                    continue;
                }

                // Giá mới: dùng website timestamp nếu có, fallback now()
                $recordedAt = $websiteTimestamp ?? now();
                SilverPriceHistory::create([
                    'source'      => 'ancarat',
                    'unit'        => $unit,
                    'buy_price'   => $buy,
                    'sell_price'  => $sell,
                    'price_date'  => $recordedAt->toDateString(),
                    'recorded_at' => $recordedAt,
                ]);

                $this->info("  ✅ [{$unit}/{$sku}] Mua=" . number_format($buy) . ' Bán=' . number_format($sell) . ' lúc ' . $recordedAt->format('H:i d/m'));
                $inserted++;
            }

        } catch (\Exception $e) {
            $this->error('💥 ' . $e->getMessage());
            Log::error('FetchAncaratSilverPrice', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }

        $summary = $inserted > 0
            ? "inserted: {$inserted} | unchanged: {$unchanged}"
            : "no changes (giá không đổi, unchanged: {$unchanged})";
        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Hoàn thành Ancarat.');
        $endAt = now()->format('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$endAt}] ✅ silver:fetch-ancarat DONE – {$summary}\n", FILE_APPEND);
        return Command::SUCCESS;
    }

    /**
     * Parse API response thành map: SKU → [sell_price, buy_price]
     * Response: [product_name, sell_price, buy_price, sku?, url?]
     */
    private function parsePriceMap(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            // Bỏ qua header, category, row không có giá
            if (!is_array($row) || count($row) < 3) {
                continue;
            }
            $name = $row[0] ?? '';
            $sell = $row[1] ?? '';
            $buy  = $row[2] ?? '';
            $sku  = $row[3] ?? null;

            if (!$sku || empty($name)) {
                continue;
            }

            $sellVal = (int) preg_replace('/[^0-9]/', '', (string) $sell);
            $buyVal  = (int) preg_replace('/[^0-9]/', '', (string) $buy);

            if ($sellVal > 0 || $buyVal > 0) {
                $map[$sku] = [$sellVal, $buyVal];
            }
        }
        return $map;
    }
}
