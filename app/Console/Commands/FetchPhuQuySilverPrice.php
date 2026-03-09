<?php

namespace App\Console\Commands;

use App\Models\SilverPriceHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchPhuQuySilverPrice extends Command
{
    protected $signature   = 'silver:fetch-phuquy';
    protected $description = 'Fetch giá bạc từ Phú Quý (giabac.vn) mỗi 30 phút';

    const BASE_URL = 'https://giabac.vn/SilverInfo';

    /**
     * Map: unit key → [filterType payload, unit label]
     */
    const UNITS = [
        'CHI' => ['filterType' => '#pills-home',   'label' => 'Chỉ'],
        'KG'  => ['filterType' => '#pills-contact', 'label' => 'Kilogram'],
        // LUONG không lưu vào DB: tính từ CHI × 10 ở tầng API
    ];

    public function handle(): int
    {
        $logFile = storage_path('logs/cron-phuquy.log');
        $startAt = now()->format('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$startAt}] ▶ silver:fetch-phuquy START\n", FILE_APPEND);

        $this->info("[{$startAt}] Bắt đầu fetch giá bạc Phú Quý...");

        $success = true;

        // Fetch chart data cho KG và CHI để lưu history (1 bảng duy nhất)
        // Lưu ý: LUONG không có endpoint riêng trong API Phú Quý → tính từ CHI × 10 ở tầng API
        $chartUnits = ['KG', 'CHI'];
        foreach ($chartUnits as $chartUnit) {
            try {
                $res = Http::timeout(15)
                    ->get(self::BASE_URL . '/GetGoldPriceChartFromSQLData', [
                        'days' => 1,
                        'type' => $chartUnit,
                    ]);

                if ($res->ok()) {
                    $data = $res->json();
                    $this->saveHistory($data, $chartUnit);
                } else {
                    $this->warn("  ❌ Chart data [{$chartUnit}] HTTP " . $res->status());
                    $success = false;
                }
            } catch (\Exception $e) {
                $this->error("  💥 Chart data [{$chartUnit}]: " . $e->getMessage());
                Log::error('FetchPhuQuySilverPrice chart error', ['unit' => $chartUnit, 'error' => $e->getMessage()]);
                $success = false;
            }
        }

        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Hoàn thành.');
        $endAt = now()->format('Y-m-d H:i:s');
        $status = $success ? 'DONE ✓' : 'DONE (có lỗi)';
        file_put_contents($logFile, "[{$endAt}] ■ silver:fetch-phuquy {$status}\n", FILE_APPEND);
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
     * Dedup theo giá: chỉ insert khi buy/sell khác với record cuối cùng trong DB.
     */
    private function saveHistory(array $data, string $unit = 'KG'): void
    {
        $dates      = $data['Dates']          ?? [];
        $buyPrices  = $data['LastBuyPrices']  ?? [];
        $sellPrices = $data['LastSellPrices'] ?? [];

        if (empty($dates)) {
            return;
        }

        // Lấy entry CUỐI cùng trong data trả về (mới nhất)
        $lastIdx = count($dates) - 1;

        $date  = $dates[$lastIdx]      ?? null;
        $buy   = isset($buyPrices[$lastIdx])  ? (int) round($buyPrices[$lastIdx])  : null;
        $sell  = isset($sellPrices[$lastIdx]) ? (int) round($sellPrices[$lastIdx]) : null;

        if (!$date || !$buy || !$sell) {
            return;
        }

        // Dedup theo giá: lấy record cuối cùng trong DB, so sánh giá
        $lastRecord = SilverPriceHistory::where('source', 'phuquy')
            ->where('unit', $unit)
            ->orderByDesc('recorded_at')
            ->first();

        if ($lastRecord && (int)$lastRecord->buy_price === $buy && (int)$lastRecord->sell_price === $sell) {
            $this->line("  ⏭  History [{$unit}]: giá không đổi (Mua=" . number_format($buy) . ' Bán=' . number_format($sell) . '), bỏ qua.');
            return;
        }

        SilverPriceHistory::create([
            'source'      => 'phuquy',
            'unit'        => $unit,
            'buy_price'   => $buy,
            'sell_price'  => $sell,
            'price_date'  => $date,
            'recorded_at' => now(),
        ]);
        $this->info("  ✅ History [{$unit}]: {$date} Mua=" . number_format($buy) . ' Bán=' . number_format($sell));
    }
}

