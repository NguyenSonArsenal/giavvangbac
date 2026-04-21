<?php

namespace App\Console\Commands;

use App\Models\SilverPriceHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchSilverPhuQuy extends Command
{
    protected $signature   = 'silver:fetch-phuquy';
    protected $description = 'Fetch giá bạc từ Phú Quý (giabac.vn) mỗi 30 phút';

    const BASE_URL = 'https://giabac.vn/SilverInfo';

    /**
     * Map: unit key để gọi API → [filterType, label, unit lưu DB, multiplier]
     * Lưu ý: API Phú Quý chỉ có endpoint CHI (& KG); LUONG = CHI × 10
     */
    const UNITS = [
        'KG'  => ['filterType' => '#pills-contact', 'label' => 'Kilogram',  'save_unit' => 'KG',    'mult' => 1 ],
        'CHI' => ['filterType' => '#pills-home',    'label' => 'Lượng (từ Chỉ)', 'save_unit' => 'LUONG', 'mult' => 10],
        // Gọi API CHI nhưng lưu vào DB là LUONG (buy/sell × 10)
    ];

    public function handle(): int
    {
        $logFile = storage_path('logs/cron-silver-phuquy.log');
        $startAt = now()->format('Y-m-d H:i:s');

        $this->info("[{$startAt}] Bắt đầu fetch giá bạc Phú Quý...");

        $success   = true;
        $inserted  = 0;
        $unchanged = 0;

        // Fetch chart data cho KG và CHI (lưu dưới dạng KG và LUONG)
        $chartUnits = ['KG', 'CHI'];
        foreach ($chartUnits as $apiUnit) {
            $cfg = self::UNITS[$apiUnit];
            try {
                $res = Http::timeout(15)
                    ->get(self::BASE_URL . '/GetGoldPriceChartFromSQLData', [
                        'days' => 1,
                        'type' => $apiUnit,
                    ]);

                if ($res->ok()) {
                    $data = $res->json();
                    $this->saveHistory($data, $cfg['save_unit'], $cfg['mult'], $inserted, $unchanged);
                } else {
                    $this->warn("  ❌ Chart data [{$apiUnit}] HTTP " . $res->status());
                    $success = false;
                }
            } catch (\Exception $e) {
                $this->error("  💥 Chart data [{$apiUnit}]: " . $e->getMessage());
                Log::error('FetchPhuQuySilverPrice chart error', ['unit' => $apiUnit, 'error' => $e->getMessage()]);
                $success = false;
            }
        }

        $summary = $inserted > 0
            ? "inserted: {$inserted} | unchanged: {$unchanged}"
            : "no changes (giá không đổi, unchanged: {$unchanged})";
        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Hoàn thành.');
        $endAt = now()->format('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$endAt}] ✅ silver:fetch-phuquy DONE – {$summary}\n", FILE_APPEND);
        return $success ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Parse HTML từ FilterData để lấy buy/sell price
     * HTML dạng: <p class="text-24px text-red">84,426,456</p>
     */
    private function parseFilterDataHtml(string $html): array
    {
        // Lấy tất cả số trong các thẻ text-24px
        preg_match_all('/<p[^>]*class="[^"]*text-24px[^"]*"[^>]*>([\d,\.]+)<\/p>/i', $html, $matches);

        if (count($matches[1]) < 2) {
            // fallback: bắt tất cả số lớn
            preg_match_all('/>([\d]{2,3}[,\.][\d,\.]+)</', $html, $matches);
        }

        $prices = [];
        foreach (($matches[1] ?? []) as $raw) {
            $val = (int) preg_replace('/[^0-9]/', '', $raw);
            if ($val > 100000) { // ít nhất 100k để loại rác
                $prices[] = $val;
            }
        }

        if (count($prices) < 2) {
            return [null, null];
        }

        return [$prices[0], $prices[1]]; // [buy, sell]
    }

    /**
     * Lưu các điểm dữ liệu từ chart API vào history.
     * @param string $saveUnit   Đơn vị lưu vào DB (KG hoặc LUONG)
     * @param int    $multiplier Nhân giá trước khi lưu (1 cho KG, 10 cho LUONG từ CHI)
     */
    private function saveHistory(array $data, string $saveUnit = 'KG', int $multiplier = 1, int &$inserted = 0, int &$unchanged = 0): void
    {
        $dates      = $data['Dates']          ?? [];
        $buyPrices  = $data['LastBuyPrices']  ?? [];
        $sellPrices = $data['LastSellPrices'] ?? [];

        if (empty($dates)) {
            return;
        }

        // Lấy entry CUỐI cùng trong data trả về (mới nhất)
        $lastIdx = count($dates) - 1;

        $dateRaw = $dates[$lastIdx]      ?? null;
        $buy     = isset($buyPrices[$lastIdx])  ? (int) round($buyPrices[$lastIdx]  * $multiplier) : null;
        $sell    = isset($sellPrices[$lastIdx]) ? (int) round($sellPrices[$lastIdx] * $multiplier) : null;

        if (!$dateRaw || !$buy || !$sell) {
            return;
        }

        // Parse thời gian từ API (Dates thường dạng "DD/MM/YYYY" hoặc "YYYY-MM-DD")
        // Giữ null nếu không parse được (để không ghi thời gian cron vào DB)
        $websiteTimestamp = null;
        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateRaw)) {
                // ISO format: "2026-04-21"
                $websiteTimestamp = Carbon::createFromFormat('Y-m-d', $dateRaw);
            } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}/', $dateRaw)) {
                // Vietnamese format: "21/04/2026" hoặc "21/04/2026 13:45"
                $fmt = str_contains($dateRaw, ' ') ? 'd/m/Y H:i' : 'd/m/Y';
                $websiteTimestamp = Carbon::createFromFormat($fmt, trim($dateRaw));
            }
        } catch (\Exception) {
            $websiteTimestamp = null;
        }

        if ($websiteTimestamp) {
            $this->line("  🕐 [{$saveUnit}] Thời gian website: " . $websiteTimestamp->format('d/m/Y H:i'));
        } else {
            $this->line("  ⚠ [{$saveUnit}] Không parse được thời gian website, sẽ giữ nguyên recorded_at cũ nếu giá không đổi");
        }

        // Dedup theo giá: lấy record cuối cùng trong DB, so sánh giá
        $lastRecord = SilverPriceHistory::where('source', 'phuquy')
            ->where('unit', $saveUnit)
            ->orderByDesc('recorded_at')
            ->first();

        if ($lastRecord && (int)$lastRecord->buy_price === $buy && (int)$lastRecord->sell_price === $sell) {
            if ($websiteTimestamp) {
                // Website có timestamp thực → cập nhật recorded_at
                $lastRecord->recorded_at = $websiteTimestamp;
                $lastRecord->save();
                $this->line("  🔄 [{$saveUnit}] giá không đổi → cập nhật recorded_at = " . $websiteTimestamp->format('H:i d/m/Y'));
            } else {
                // Không có timestamp website → giữ nguyên, không ghi gì
                $this->line("  ⏭  [{$saveUnit}] giá không đổi, không có timestamp website → giữ nguyên");
            }
            $unchanged++;
            return;
        }

        // Giá mới: dùng website timestamp nếu có, fallback now()
        $recordedAt = $websiteTimestamp ?? now();
        SilverPriceHistory::create([
            'source'      => 'phuquy',
            'unit'        => $saveUnit,
            'buy_price'   => $buy,
            'sell_price'  => $sell,
            'price_date'  => $recordedAt->toDateString(),
            'recorded_at' => $recordedAt,
        ]);
        $this->info("  ✅ [{$saveUnit}] " . $recordedAt->format('d/m') . ' Mua=' . number_format($buy) . ' Bán=' . number_format($sell) . ' lúc ' . $recordedAt->format('H:i'));
        $inserted++;
    }
}

