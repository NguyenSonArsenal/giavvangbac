<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSilverPriceHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('silver_price_history', function (Blueprint $table) {
            $table->id();
            $table->string('source', 50)->default('phuquy');
            $table->string('unit', 20)->comment('KG | LUONG | CHI');
            $table->bigInteger('buy_price')->comment('giá mua (VND)');
            $table->bigInteger('sell_price')->comment('giá bán (VND)');
            $table->date('price_date')->comment('ngày ghi nhận');
            $table->timestamp('recorded_at')->comment('thời điểm cụ thể');
            $table->timestamps();

            $table->index(['source', 'unit', 'price_date']);
            $table->index(['source', 'unit', 'recorded_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('silver_price_history');
    }
}
