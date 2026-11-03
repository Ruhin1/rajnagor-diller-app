<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('morich_daily_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('morich_daily_sale_id')->constrained('morich_daily_sales')->onDelete('cascade');
            $table->foreignId('morich_product_id')->constrained('morich_products')->onDelete('cascade');
            $table->unsignedInteger('quantity_sold_in_grams');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('morich_daily_sale_items');
    }
};
