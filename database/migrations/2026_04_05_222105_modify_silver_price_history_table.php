<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySilverPriceHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('silver_price_history', function (Blueprint $table) {
            $table->dropIndex(['source', 'unit', 'price_date']);
            $table->dropIndex(['source', 'unit', 'recorded_at']);
        });

        Schema::rename('silver_price_history', 'metal_prices');

        Schema::table('metal_prices', function (Blueprint $table) {
            $table->string('metal_type', 20)->default('silver')->after('id');
            $table->index(['metal_type', 'source', 'unit', 'price_date']);
            $table->index(['metal_type', 'source', 'unit', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('metal_prices', function (Blueprint $table) {
            $table->dropIndex(['metal_type', 'source', 'unit', 'price_date']);
            $table->dropIndex(['metal_type', 'source', 'unit', 'recorded_at']);
            $table->dropColumn('metal_type');
        });

        Schema::rename('metal_prices', 'silver_price_history');

        Schema::table('silver_price_history', function (Blueprint $table) {
            $table->index(['source', 'unit', 'price_date']);
            $table->index(['source', 'unit', 'recorded_at']);
        });
    }
}
