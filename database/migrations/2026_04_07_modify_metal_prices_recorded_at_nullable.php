<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Đổi cột recorded_at trong bảng metal_prices:
     *   - Bỏ DEFAULT CURRENT_TIMESTAMP
     *   - Bỏ ON UPDATE CURRENT_TIMESTAMP
     *   - Cho phép NULL
     * Mục đích: recorded_at sẽ lưu đúng thời gian từ API nguồn
     *            thay vì bị ghi đè bởi thời gian server khi UPDATE.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `metal_prices`
            MODIFY COLUMN `recorded_at` DATETIME NULL DEFAULT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE `metal_prices`
            MODIFY COLUMN `recorded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }
};
