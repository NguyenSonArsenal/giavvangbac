<?php

namespace App\Console\Commands;

use App\Models\SilverPriceHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchSilverDoji extends Command
{
    protected $signature   = 'silver:fetch-doji';
    protected $description = 'Fetch giá bạc từ DOJI (giabac.doji.vn) mỗi 30 phút';

    // Cache-buster: timestamp dạng milliseconds, cập nhật mỗi lần chạy
    // DOJI chỉ có Lượng (không có KG riêng)
    // 5 Lượng được tính = 1 Lượng × 5 ở tầng JS/API
    const ENDPOINTS = [
        'LUONG' => 'https://giabac.doji.vn/data/DataBac9991Luong.txt',
    ];

    public function handle(): int
    {
        $logFile = storage_path('logs/cron-silver-doji.log');
        $startAt = now()->format('Y-m-d H:i:s');

        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Fetch giá bạc DOJI...');

        $cacheBuster = now()->timestamp * 1000;
        $success     = true;
        $inserted    = 0;
        $unchanged   = 0;

        foreach (self::ENDPOINTS as $unit => $url) {
            try {
                $res = Http::timeout(15)->get($url, ['t' => $cacheBuster]);

                if (!$res->ok()) {
                    $this->warn("  ❌ [{$unit}] HTTP " . $res->status());
                    $success = false;
                    continue;
                }

                $rows = $this->parseRows($res->body());
                if (empty($rows)) {
                    $this->warn("  ⚠ [{$unit}] Không parse được data");
                    continue;
                }

                // Lấy entry cuối cùng = giá hiện tại
                $last = end($rows);
                [$buy, $sell, $datetime] = $last;

                // Parse timestamp từ website (format: "HH:MM:SS DD/MM/YYYY")
                // Ví dụ: "13:45:30 21/04/2026" – giữ null nếu không parse được
                $websiteTimestamp = null;
                if ($datetime) {
                    try {
                        // Bỏ giây nếu có (HH:MM:SS → HH:MM)
                        $dtClean = preg_replace('/(\d{2}:\d{2}):\d{2}/', '$1', trim($datetime));
                        $websiteTimestamp = Carbon::createFromFormat('H:i d/m/Y', $dtClean);
                    } catch (\Exception) {
                        $websiteTimestamp = null;
                    }
                }

                if ($websiteTimestamp) {
                    $this->line("  🕐 [{$unit}] Thời gian website: " . $websiteTimestamp->format('d/m/Y H:i'));
                } else {
                    $this->line("  ⚠ [{$unit}] Không parse được thời gian website, giữ nguyên recorded_at cũ");
                }

                // Lưu history – dedup theo giá
                $lastRecord = SilverPriceHistory::where('source', 'doji')
                    ->where('unit', $unit)
                    ->orderByDesc('recorded_at')
                    ->first();

                if ($lastRecord && (int)$lastRecord->buy_price === $buy && (int)$lastRecord->sell_price === $sell) {
                    if ($websiteTimestamp) {
                        // Giá không đổi, cập nhật recorded_at theo thời gian website (không dùng now())
                        $lastRecord->recorded_at = $websiteTimestamp;
                        $lastRecord->save();
                        $this->line("  🔄 [{$unit}] giá không đổi → cập nhật recorded_at = " . $websiteTimestamp->format('H:i d/m/Y'));
                    } else {
                        // Không có timestamp website → giữ nguyên, không ghi gì
                        $this->line("  ⏭  [{$unit}] giá không đổi, không có timestamp website → giữ nguyên");
                    }
                    $unchanged++;
                    continue;
                }

                // Giá mới: dùng timestamp website nếu có, fallback now()
                $recordedAt = $websiteTimestamp ?? now();
                SilverPriceHistory::create([
                    'source'      => 'doji',
                    'unit'        => $unit,
                    'buy_price'   => $buy,
                    'sell_price'  => $sell,
                    'price_date'  => $recordedAt->toDateString(),
                    'recorded_at' => $recordedAt,
                ]);
                $this->info("  ✅ [{$unit}] Mua=" . number_format($buy) . ' Bán=' . number_format($sell) . ' lúc ' . $recordedAt->format('H:i d/m'));
                $inserted++;

            } catch (\Exception $e) {
                $this->error("  💥 [{$unit}]: " . $e->getMessage());
                Log::error('FetchDojiSilverPrice', ['unit' => $unit, 'error' => $e->getMessage()]);
                $success = false;
            }
        }

        $summary = $inserted > 0
            ? "inserted: {$inserted} | unchanged: {$unchanged}"
            : "no changes (giá không đổi, unchanged: {$unchanged})";
        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Hoàn thành DOJI.');
        file_put_contents($logFile, '[' . now()->format('Y-m-d H:i:s') . "] ✅ silver:fetch-doji DONE – {$summary}\n", FILE_APPEND);
        return $success ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Parse text file: mỗi dòng = "buy|sell|HH:MM:SS DD/MM/YYYY"
     * Trả về array of [buy, sell, datetime_string]
     */
    public function parseRows(string $body): array
    {
        // Xóa BOM nếu có
        $body = ltrim($body, "\xEF\xBB\xBF");
        $lines = preg_split('/\r?\n/', trim($body));
        $rows  = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            $parts = explode('|', $line);
            if (count($parts) < 3) {
                continue;
            }
            $buy  = (int) preg_replace('/[^0-9]/', '', $parts[0]);
            $sell = (int) preg_replace('/[^0-9]/', '', $parts[1]);
            $dt   = trim($parts[2]);

            if ($buy > 0 && $sell > 0) {
                $rows[] = [$buy, $sell, $dt];
            }
        }

        return $rows;
    }
}
