<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\GoldPriceHistory;
use Carbon\Carbon;

class FetchGoldBtmh extends Command
{
    protected $signature   = 'gold:fetch-btmh';
    protected $description = 'Fetch giá vàng realtime từ Bảo Tín Mạnh Hải – dùng GraphQL API mới';

    /**
     * Các mã vàng (code) cần lưu vào DB:
     *   code (API) => unit (DB)
     * Chỉ lưu các loại hợp lệ (sell_price > 1)
     */
    const GOLD_TYPES = [
        'KGB' => 'KGB',  // Nhẫn Tròn ép vỉ (Kim Gia Bảo) 24K (999.9)
    ];

    const GRAPHQL_ENDPOINT = 'https://baotinmanhhai.vn/api/graphql';

    const GRAPHQL_QUERY = <<<'GQL'
query GetGoldRates {
  goldRates {
    items {
      code
      name
      buy_price
      sell_price
      unit
      last_updated
    }
  }
}
GQL;

    public function handle(): int
    {
        $logFile = storage_path('logs/cron-gold-btmh.log');
        $startAt = now()->format('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$startAt}] ▶ gold:fetch-btmh START\n", FILE_APPEND);

        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Fetch giá vàng BTMH (GraphQL)...');
        $inserted  = 0;
        $unchanged = 0;

        try {
            // Gọi GraphQL API
            // withoutVerifying() để bỏ qua SSL verify trên môi trường local (Windows)
            $http = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                    'Referer'      => 'https://baotinmanhhai.vn/vi/bang-gia-vang',
                ]);

            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }

            $res = $http->post(self::GRAPHQL_ENDPOINT, [
                'query' => self::GRAPHQL_QUERY,
            ]);

            if (!$res->ok()) {
                $this->error('❌ HTTP ' . $res->status());
                Log::error('FetchBtmhGoldPrice: HTTP ' . $res->status());
                file_put_contents($logFile, "[{$startAt}] ❌ HTTP {$res->status()}\n", FILE_APPEND);
                return 1;
            }

            $items = $res->json('data.goldRates.items') ?? [];

            if (empty($items)) {
                $this->warn('⚠ GraphQL trả về không có items');
                file_put_contents($logFile, "[{$startAt}] ⚠ Không có items\n", FILE_APPEND);
                return 1;
            }

            // Lập chỉ mục theo code để tìm nhanh
            $indexed = [];
            foreach ($items as $item) {
                $indexed[$item['code']] = $item;
            }

            foreach (self::GOLD_TYPES as $code => $unit) {
                $this->line("  ── code={$code} → unit={$unit}");

                if (!isset($indexed[$code])) {
                    $this->warn("  ⚠ Không tìm thấy code={$code} trong response");
                    continue;
                }

                $item      = $indexed[$code];
                $buyPrice  = (int) ($item['buy_price']  ?? 0);
                $sellPrice = (int) ($item['sell_price'] ?? 0);

                // Bỏ qua nếu sell_price là placeholder (= 1) hoặc giá = 0
                if ($buyPrice <= 0 || $sellPrice <= 1) {
                    $this->warn("  ⚠ [{$code}] Giá không hợp lệ (Mua={$buyPrice} Bán={$sellPrice}), bỏ qua");
                    continue;
                }

                // Parse last_updated từ API (format: "2026-04-13 09:41:33.327")
                $recordedAt = null;
                $rawDate    = $item['last_updated'] ?? null;
                if ($rawDate) {
                    try {
                        // Cắt bỏ phần milliseconds nếu có
                        $recordedAt = Carbon::createFromFormat('Y-m-d H:i:s', substr($rawDate, 0, 19));
                    } catch (\Exception) {
                        $recordedAt = now();
                    }
                }
                $recordedAt = $recordedAt ?? now();

                $buyForLog  = number_format($buyPrice);
                $sellForLog = number_format($sellPrice);

                // So sánh với bản ghi cuối trong DB để tránh duplicate
                $lastRecord = GoldPriceHistory::where('source', 'btmh')
                    ->where('unit', $unit)
                    ->orderByDesc('recorded_at')
                    ->first();

                if (
                    $lastRecord
                    && (int) $lastRecord->buy_price  === $buyPrice
                    && (int) $lastRecord->sell_price === $sellPrice
                ) {
                    $this->line("  ⏭  [{$unit}] Giá không thay đổi (Mua={$buyForLog} Bán={$sellForLog}), bỏ qua");
                    $unchanged++;
                } else {
                    GoldPriceHistory::create([
                        'source'      => 'btmh',
                        'unit'        => $unit,
                        'buy_price'   => $buyPrice,
                        'sell_price'  => $sellPrice,
                        'price_date'  => $recordedAt->toDateString(),
                        'recorded_at' => $recordedAt,
                    ]);
                    $this->info("  ✅ [{$unit}] saved (Mua={$buyForLog} Bán={$sellForLog})");
                    $inserted++;
                }
            }

        } catch (\Exception $e) {
            $this->error('💥 Error: ' . $e->getMessage());
            Log::error('FetchBtmhGoldPrice', ['error' => $e->getMessage()]);
            file_put_contents($logFile, "[{$startAt}] 💥 {$e->getMessage()}\n", FILE_APPEND);
            return 1;
        }

        $summary = $inserted > 0
            ? "inserted: {$inserted} | unchanged: {$unchanged}"
            : "no changes (giá không đổi, unchanged: {$unchanged})";
        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Hoàn thành BTMH Gold.');
        file_put_contents($logFile, '[' . now()->format('Y-m-d H:i:s') . "] ✅ gold:fetch-btmh DONE – {$summary}\n", FILE_APPEND);
        return 0;
    }
}
