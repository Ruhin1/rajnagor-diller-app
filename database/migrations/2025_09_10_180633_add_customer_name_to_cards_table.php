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
        Schema::table('cards', function (Blueprint $table) {
            // 'id' কলামের পরে 'customer_name' নামে একটি নতুন কলাম যোগ করা হচ্ছে
            $table->string('customer_name')->nullable()->after('id'); // আপনি চাইলে after('id') ছাড়াও লিখতে পারেন
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cards', function (Blueprint $table) {
            // যদি মাইগ্রেশন রোলব্যাক করা হয়, তাহলে এই কলামটি মুছে যাবে
            $table->dropColumn('customer_name');
        }); 
    }
};
 