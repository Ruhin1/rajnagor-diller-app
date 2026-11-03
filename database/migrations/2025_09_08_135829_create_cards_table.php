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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
	        $table->string('product_name');
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->string('used_by')->nullable();
	        $table->boolean('sr_pay')->default(false);
	        $table->string(column: 'identy_number');
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
        Schema::dropIfExists('cards');
    }
};
