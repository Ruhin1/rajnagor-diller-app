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
        Schema::table('sales', function (Blueprint $table) {
              $table->string('guest_name')->nullable()->after('srname');
              $table->string('guest_phone')->nullable()->after('guest_name');
              $table->string('guest_sell_type')->nullable()->after('guest_name');
        });
    } 

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
             $table->dropColumn(['guest_name', 'guest_phone','guest_sell_type']);
        });
    }
};
