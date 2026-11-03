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
   public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->string('awards')->nullable()->after('id'); 
            // এখানে after('id') দিলে id কলামের পরে যুক্ত হবে, চাইলে অন্য ফিল্ডও দিতে পারেন
        });

        Schema::table('card_tocens', function (Blueprint $table) {
            $table->string('awards')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn('awards');
        });

        Schema::table('card_tocens', function (Blueprint $table) {
            $table->dropColumn('awards');
        });
    }  
};
 