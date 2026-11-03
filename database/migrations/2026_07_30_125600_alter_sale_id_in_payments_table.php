<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Foreign key remove
            $table->dropForeign(['sale_id']);

            // Change column
            $table->unsignedBigInteger('sale_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Restore column
            $table->unsignedBigInteger('sale_id')->nullable(false)->change();

            // Restore foreign key
            $table->foreign('sale_id')
                  ->references('id')
                  ->on('sales')
                  ->cascadeOnDelete();
        });
    }
};