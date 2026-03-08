<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSilverPricesTable extends Migration
{
    public function up()
    {
        Schema::create('silver_prices', function (Blueprint $table) {
            $table->id();
            $table->string('source', 50)->default('phuquy')->comment('nguồn dữ liệu');
            $table->string('product_name', 200)->comment('tên sản phẩm');
            $table->string('unit', 20)->comment('CHI | LUONG | KG');
            $table->bigInteger('buy_price')->comment('giá mua (VND)');
            $table->bigInteger('sell_price')->comment('giá bán (VND)');
            $table->timestamp('recorded_at')->comment('thời điểm ghi nhận');
            $table->timestamps();

            $table->index(['source', 'unit']);
            $table->unique(['source', 'unit'], 'uq_source_unit');
        });
    }

    public function down()
    {
        Schema::dropIfExists('silver_prices');
    }
}
