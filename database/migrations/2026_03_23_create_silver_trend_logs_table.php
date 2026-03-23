<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('silver_trend_logs', function (Blueprint $table) {
            $table->id();
            $table->text('analysis');                         // Nội dung nhận định AI
            $table->string('source', 20)->default('gemini');  // gemini | fallback
            $table->decimal('pct_change', 8, 2)->nullable();  // % thay đổi 7 ngày
            $table->string('trend', 20)->nullable();          // tăng | giảm | đi ngang
            $table->bigInteger('high_price')->nullable();     // Giá cao nhất
            $table->bigInteger('low_price')->nullable();      // Giá thấp nhất
            $table->bigInteger('latest_price')->nullable();   // Giá mới nhất
            $table->json('raw_stats')->nullable();            // JSON thống kê đầy đủ
            $table->boolean('is_accurate')->nullable();       // Admin đánh giá đúng/sai (null = chưa review)
            $table->text('admin_note')->nullable();           // Ghi chú admin
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('silver_trend_logs');
    }
};
