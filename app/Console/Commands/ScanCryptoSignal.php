<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\BinanceAnalyzerService;
use App\Models\CryptoSignalLog;
use Carbon\Carbon;

/**
 * ScanCryptoSignal
 * ─────────────────────────────────────────────────────────────────────────────
 * Chạy mỗi 5 phút, phân tích chart các cặp coin trên Binance.
 * Nếu phát hiện tín hiệu đáng xem (score >= 3) → ghi log file + lưu DB.
 *
 * Chạy tay:  php artisan crypto:scan-signal
 * Tất cả:    php artisan crypto:scan-signal --all
 * 1 coin:    php artisan crypto:scan-signal --symbol=BTCUSDT
 */
class ScanCryptoSignal extends Command
{
    protected $signature = 'crypto:scan-signal
                            {--symbol=      : Chỉ quét 1 symbol cụ thể (vd: BNBUSDT)}
                            {--all          : Quét tất cả symbols trong config}
                            {--no-db        : Không ghi vào database, chỉ log file}
                            {--test-discord : Test gửi tin nhắn Discord (không scan signal)}';

    protected $description = 'Quet tín hieu mua crypto tren Binance moi 5 phut (MA + RSI)';

    // ── Chỉ scan BNB ──────────────────────────────────────────────────────────
    const SYMBOLS = [
        'BNBUSDT',   // BNB — tập trung học 1 coin trước
    ];

    // Khung nến: 1h (phù hợp để học, ít nhiễu hơn 15m)
    // 15m = ngắn hạn, nhiều tín hiệu giả
    // 1h  = trung hạn, tín hiệu đáng tin cậy hơn
    // 4h  = dài hạn, ít lệnh nhưng chắc chắn hơn
    const INTERVAL = '1h';    // ← Anh đang xem khung 1 GIỜ
    const LIMIT    = 150;     // Số nến cần lấy (đủ tính MA99 + RSI14)

    // Chỉ lưu DB + log file nếu score >= ngưỡng này
    const MIN_SCORE_TO_LOG = 3;

    public function handle(BinanceAnalyzerService $analyzer): int
    {
        $logFile = storage_path('logs/cron-crypto-signal.log');

        // ── TEST DISCORD: chạy thử gửi tin nhắn ────────────────────────
        // Chạy: php artisan crypto:scan-signal --test-discord
        if ($this->option('test-discord')) {
            $ok = $this->sendDiscord('Hi Discord! 👋 Từ server giá vàng bạc - test kết nối thành công!');
            if ($ok) {
                $this->info('✅ Gửi Discord thành công!');
            } else {
                $this->error('❌ Gửi Discord thất bại! Kiểm tra DISCORD_WEBHOOK_URL trong .env');
            }
            return 0;
        }

        // ── Helper ghi log đồng thời terminal + file ─────────────────────────
        $log = function (string $msg, string $level = 'line') use ($logFile) {
            match ($level) {
                'info'  => $this->info($msg),
                'warn'  => $this->warn($msg),
                'error' => $this->error($msg),
                default => $this->line($msg),
            };
            file_put_contents($logFile, $msg . "\n", FILE_APPEND);
        };

        // ── Xác định danh sách symbol cần quét ───────────────────────────────
        if ($this->option('symbol')) {
            $symbols = [strtoupper($this->option('symbol'))];
        } else {
            $symbols = self::SYMBOLS;
        }

        $now = Carbon::now();
        $log("\n" . str_repeat('=', 65));
        $log("[{$now->format('Y-m-d H:i:s')}] BNB/USDT SIGNAL SCAN");
        $log("Khung nen : " . self::INTERVAL . " (moi 1 cay nen = 1 GIO giao dich)");
        $log("Chi bao   : MA7 | MA25 | MA99 | RSI(14)");
        $log("Muc tieu  : 100k-300k VND/ngay | Von 50tr");
        $log(str_repeat('=', 65));

        // ── [TEST] Ping Discord mỗi lần cron chạy ────────────────────────────
        // TODO: Xóa dòng này sau khi confirm cron + Discord hoạt động ổn
//        $this->sendDiscord("⏱️ Cron ScanCryptoSignal chạy lúc: " . $now->format('H:i:s d/m/Y'));

        $totalSignals = 0;

        foreach ($symbols as $symbol) {
            $log("\n>> Phan tich BNB/USDT — Khung {$symbol} [" . self::INTERVAL . "]:");
            $log("   (Moi cay nen = 1 gio | Quet tu dong moi 5 phut)");

            try {
                $result = $analyzer->analyze($symbol, self::INTERVAL, self::LIMIT);

                if (!$result) {
                    $log("   [!] Khong lay duoc du lieu tu Binance [{$symbol}]", 'warn');
                    continue;
                }

                // ── In tóm tắt ra terminal ────────────────────────────────────
                $bar = str_repeat('█', max(0, $result['score']))
                     . str_repeat('░', max(0, 10 - $result['score']));

                // Giải thích MA cho anh dễ hiểu
                $trendMA99 = $result['price'] > $result['ma99'] ? 'TREN (Trend tang)' : 'DUOI (Trend yeu!)';
                $rsiZone   = $result['rsi'] < 30  ? 'OVERSOLD - Vung mua tot!'
                           : ($result['rsi'] < 50  ? 'Hoi thap - Theo doi'
                           : ($result['rsi'] < 70  ? 'Binh thuong'
                           : 'OVERBOUGHT - Khong mua!'));

                $log("   --- GIA & CHI BAO ---");
                $log(sprintf("   Gia hien tai : %s USDT", $result['price']));
                $log(sprintf("   MA7  (2 gio) : %s", $result['ma7']));
                $log(sprintf("   MA25 (1 ngay): %s", $result['ma25']));
                $log(sprintf("   MA99 (4 ngay): %s  => Gia dang %s", $result['ma99'], $trendMA99));
                $log(sprintf("   RSI  (14h)   : %s  => %s", $result['rsi'], $rsiZone));
                $log("   --- KET QUA ---");
                $log("   Diem: [{$bar}] {$result['score']}/10  =>  {$result['signal_label']}");

                // ── Chỉ ghi log chi tiết nếu đáng xem ───────────────────────
                if ($result['score'] >= self::MIN_SCORE_TO_LOG) {
                    $totalSignals++;

                    // Lý do
                    if (!empty($result['reasons'])) {
                        $log("   [+] Dieu kien tot:");
                        foreach ($result['reasons'] as $r) {
                            $log("       + {$r}");
                        }
                    }

                    // Cảnh báo
                    if (!empty($result['warnings'])) {
                        $log("   [!] Canh bao:", 'warn');
                        foreach ($result['warnings'] as $w) {
                            $log("       ! {$w}", 'warn');
                        }
                    }

                    // Gợi ý hành động
                    $this->logRecommendation($log, $result);

                    // ── Ghi vào DB ───────────────────────────────────────────
                    if (!$this->option('no-db')) {
                        $this->saveToDb($result, $now);
                        $log("   [DB] Da luu vao crypto_signal_logs", 'info');
                    }

                } else {
                    $log("   [-] Score thap ({$result['score']}), bo qua — khong du dieu kien mua");
                }

                // ── Luôn gửi kết quả lên Discord (test + học) ────────────────
                // TODO: Sau khi ổn định, đổi thành chỉ gửi khi score >= 5
                $this->notifyDiscord($result);
                $log("   [DISCORD] Da gui ket qua", 'info');

            } catch (\Exception $e) {
                $log("   [ERROR] {$symbol}: " . $e->getMessage(), 'error');
                Log::error("ScanCryptoSignal [{$symbol}]", ['error' => $e->getMessage()]);
            }
        }

        $log("\n" . str_repeat('-', 65));
        $log("Ket qua: {$totalSignals} tin hieu dang xem trong lan quet nay.");
        $log(str_repeat('-', 65) . "\n");

        return 0;
    }

    // ── Ghi gợi ý hành động cụ thể ───────────────────────────────────────────
    private function logRecommendation(callable $log, array $result): void
    {
        $price = $result['price'];
        $ma25  = $result['ma25'];
        $ma99  = $result['ma99'];

        // Gợi ý chốt lời: +0.6% (tương đương ~$3-5/BNB, $150-250/BTC)
        $targetPct  = 0.006;
        $stopPct    = 0.004;
        $target     = round($price * (1 + $targetPct), 4);
        $stopLoss   = round($price * (1 - $stopPct), 4);

        $log("   -------------------------------------------------------");
        $log("   => GOI Y HANH DONG:");
        $log("   => Gia hien tai : {$price}");
        $log("   => Chot loi     : {$target}  (+0.6%)");
        $log("   => Cat lo       : {$stopLoss}  (-0.4%)");
        $log("   => Ty le RR     : 1.5:1  (Thuong/Rui ro)");

        if ($result['signal_type'] === 'STRONG_BUY') {
            $log("   => ** MUA MANH — Tu tin vao lenh! **", 'info');
        } elseif ($result['signal_type'] === 'BUY') {
            $log("   => *  Xem xet mua — Kiem tra them chart truoc khi vao", 'info');
        } else {
            $log("   => Quan sat them, chua nen vao lenh ngay", 'warn');
        }
        $log("   -------------------------------------------------------");
    }

    // ── Lưu kết quả vào DB ───────────────────────────────────────────────────
    private function saveToDb(array $result, Carbon $now): void
    {
        // Tỷ lệ chốt lời / cắt lỗ
        $targetPct   = 1.0;  // +1.0% chốt lời  (~$6.3/BNB × 3.2 = ~$20 ≈ 500k VND)
        $stopPct     = 0.5;  // -0.5% cắt lỗ    (~$3.1/BNB × 3.2 = ~$10 ≈ 250k VND)
        $targetPrice = round($result['price'] * (1 + $targetPct / 100), 4);
        $stopPrice   = round($result['price'] * (1 - $stopPct  / 100), 4);

        CryptoSignalLog::create([
            'symbol'          => $result['symbol'],
            'interval'        => $result['interval'],
            'price'           => $result['price'],
            'ma7'             => $result['ma7'],
            'ma25'            => $result['ma25'],
            'ma99'            => $result['ma99'],
            'rsi'             => $result['rsi'],
            'volume_current'  => $result['volume_current'],
            'volume_avg'      => $result['volume_avg'],
            'score'           => $result['score'],
            'target_price'    => $targetPrice,
            'stop_loss_price' => $stopPrice,
            'target_pct'      => $targetPct,
            'stop_pct'        => $stopPct,
            'result'          => 'PENDING',
            'signal_type'     => $result['signal_type'],
            'reasons'         => json_encode($result['reasons'],  JSON_UNESCAPED_UNICODE),
            'warnings'        => json_encode($result['warnings'], JSON_UNESCAPED_UNICODE),
            'scanned_at'      => $now,
        ]);
    }

    // ── Format và gửi thông báo signal lên Discord ────────────────────────────
    private function notifyDiscord(array $result): void
    {
        $score = $result['score'];
        $rsi   = $result['rsi'];

        // Icon + label theo score
        if ($score >= 7) {
            $icon  = '🔥';
            $label = 'STRONG BUY — Tin hieu MANH!';
        } elseif ($score >= 5) {
            $icon  = '⚠️';
            $label = 'XEM XET MUA — Kiem tra them chart';
        } elseif ($score >= 3) {
            $icon  = '👀';
            $label = 'THEO DOI — Chua du dieu kien';
        } else {
            $icon  = '😴';
            $label = 'CHUA CO TIN HIEU — Cho them';
        }

        // Score bar
        $bar = str_repeat('█', $score) . str_repeat('░', 10 - $score);

        // RSI zone
        $rsiNote = $rsi < 30 ? '🟢 Oversold-Vung mua tot'
                 : ($rsi < 50 ? '🟡 Thap-Theo doi'
                 : ($rsi < 70 ? '⚪ Binh thuong'
                 : '🔴 Overbought-Tranh mua'));

        $msg  = "{$icon} **{$result['symbol']}** [{$result['interval']}]\n";
        $msg .= "**{$label}**\n";
        $msg .= "─────────────────────────────\n";
        $msg .= "💰 Gia   : \${$result['price']} USDT\n";
        $msg .= "📊 Score : [{$bar}] {$score}/10\n";
        $msg .= "📈 MA7   : {$result['ma7']}\n";
        $msg .= "📈 MA25  : {$result['ma25']}\n";
        $msg .= "📈 MA99  : {$result['ma99']}\n";
        $msg .= "💡 RSI   : {$rsi} — {$rsiNote}\n";

        // Chỉ hiện target/stop loss khi đáng xem
        if ($score >= self::MIN_SCORE_TO_LOG) {
            $targetPct   = 1.0;
            $stopPct     = 0.5;
            $targetPrice = round($result['price'] * (1 + $targetPct / 100), 4);
            $stopPrice   = round($result['price'] * (1 - $stopPct  / 100), 4);

            $msg .= "─────────────────────────────\n";
            $msg .= "🎯 Chot loi : \${$targetPrice}  (+{$targetPct}%)\n";
            $msg .= "🛡️ Cat lo   : \${$stopPrice}  (-{$stopPct}%)\n";
            $msg .= "⚖️ RR Ratio : 2:1\n";
        }

        $msg .= "─────────────────────────────\n";
        $msg .= "⏰ " . now()->format('H:i:s d/m/Y');

        $this->sendDiscord($msg);
    }

    // ── Gửi tin nhắn lên Discord qua Webhook ─────────────────────────────────
    /**
     * Gửi tin nhắn lên Discord channel qua Webhook URL.
     *
     * Cấu hình trong .env:
     *   DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/xxx/yyy
     *
     * @param  string $message  Nội dung tin nhắn (plain text)
     * @return bool             true nếu gửi thành công (HTTP 204)
     */
    private function sendDiscord(string $message): bool
    {
        $webhookUrl = getConstant('DISCORD_WEBHOOK_URL', '');

        if (!$webhookUrl) {
            $this->warn('  ⚠ Thiếu DISCORD_WEBHOOK_URL trong .env');
            return false;
        }

        // Discord Webhook nhận JSON với field "content"
        $payload = json_encode(['content' => $message]);

        $ch = curl_init($webhookUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            $this->error('  💥 cURL error: ' . $curlErr);
            Log::error('sendDiscord cURL error', ['error' => $curlErr]);
            return false;
        }

        // Discord trả về HTTP 204 (No Content) khi gửi thành công
        if ($httpCode !== 204) {
            $this->error('  ❌ Discord Webhook lỗi HTTP ' . $httpCode . ': ' . $response);
            Log::error('sendDiscord error', ['http_code' => $httpCode, 'response' => $response]);
            return false;
        }

        return true;
    }
}
