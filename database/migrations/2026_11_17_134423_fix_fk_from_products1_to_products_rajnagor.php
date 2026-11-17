<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ⚠️ এই প্রজেক্টে স্টক টেবিলের নাম stock_movements
        $tables = ['stock_movements', 'sels_returns', 'sale_items'];

        // ১. পুরনো ভুল FK ড্রপ
        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });
        }

        // ২. সঠিক টেবিলে নতুন FK
        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->foreign('product_id')
                      ->references('id')
                      ->on('products')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        $tables = ['stock_movements', 'sels_returns', 'sale_items'];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });
        }

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->foreign('product_id')
                      ->references('id')
                      ->on('products1')
                      ->onDelete('cascade');
            });
        }
    }
};