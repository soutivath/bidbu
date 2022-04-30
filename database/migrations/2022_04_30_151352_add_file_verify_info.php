<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileVerifyInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('verifies', function (Blueprint $table) {
            $table->string("verify_name")->nullable();
            $table->string("verify_surname")->nullable();
            $table->string("verify_phone_number")->nullable();
            $table->string("verify_gender")->nullable();
            $table->date("verify_date_of_birth")->nullable();

            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('verifies', function (Blueprint $table) {
            $table->dropColumn(["verify_name","verify_surname","verify_phone_number","verify_gender","verify_date_of_birth"]);
        });
    }
}
