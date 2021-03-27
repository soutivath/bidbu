<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInfoToBuddhist extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('buddhists', function (Blueprint $table) {
            $table->string("pay_choice");
            $table->string("bank_name")->nullable();
            $table->string("account_name")->nullable();
            $table->string("account_number")->nullable();
            
            $table->string("sending_choice");
            $table->string("place_send")->nullable();
            $table->string("tel")->nullable();
            $table->string("more_info")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('buddhists', function (Blueprint $table) {
            $table->dropColumn(['pay_choice','bank_name','account_name','account_number','sending_choice','place_send','tel','more_info']);
        });
    }
}
