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
        Schema::create('morich_daily_sales', function (Blueprint $table) {
            $table->id();
            $table->date('sale_date'); // কোন তারিখের বিক্রি
            $table->text('notes')->nullable(); // ঐচ্ছিক নোট
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // কোন ইউজার এন্ট্রি দিয়েছে
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
        Schema::dropIfExists('morich_daily_sales');
    }
};
