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
        Schema::create('custom_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_customer_id')->constrained('custom_customers')->onDelete('cascade');
            $table->enum('type', ['debit', 'credit']); // debit = দেনা/বিল, credit = জমা
            $table->decimal('amount', 12, 2);
            $table->text('description')->nullable();
            $table->date('transaction_date');
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
        Schema::dropIfExists('custom_transactions');
    }
};
