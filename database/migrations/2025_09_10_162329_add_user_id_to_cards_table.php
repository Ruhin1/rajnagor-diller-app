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
            // 'id' কলামের পরে 'user_id' নামে একটি নতুন কলাম যোগ করুন
            $table->foreignId('user_id')
                  ->nullable() // যদি টেবিলে আগে থেকেই ডেটা থাকে, nullable() দেওয়া নিরাপদ
                  ->after('id') // টেবিলের কোথায় কলামটি যোগ হবে (ঐচ্ছিক)
                  ->constrained('users') // users টেবিলের id কলামের সাথে যুক্ত হবে
                  ->onDelete('cascade'); // যদি কোনো ইউজার ডিলিট হয়, তার সব কার্ডও ডিলিট হয়ে যাবে
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
   public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            // প্রথমে foreign key constraint টি ড্রপ করতে হবে
            $table->dropForeign(['user_id']);
            // এরপর কলামটি ড্রপ করতে হবে
            $table->dropColumn('user_id');
        });
    }
};
