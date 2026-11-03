<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // প্রোডাক্ট ডিলিট হলে তার লগও ক্যাসকেডে মুছে যাবে
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            $table->enum('type', ['in', 'out']); // স্টক বৃদ্ধি না হ্রাস
            $table->integer('quantity');         // এই মুভমেন্টে কত ইউনিট বাড়ল/কমলো (সবসময় পজিটিভ সংখ্যা)
            $table->integer('old_stock');        // পরিবর্তনের আগের স্টক
            $table->integer('new_stock');        // পরিবর্তনের পরের স্টক

            $table->string('note')->nullable();  // ঐচ্ছিক কারণ/মন্তব্য

            // কে এই পরিবর্তন করেছে — users টেবিল না থাকলে বা কলামের নাম ভিন্ন হলে
            // এই লাইনটা সরিয়ে দিন বা টেবিলের নাম ঠিক করে নিন
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};