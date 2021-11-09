<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuddhistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buddhists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('detail');
            $table->float('price');
            $table->float('highest_price');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string("active")->default("1");
            $table->string("winner_fcm_token")->default("empty");
            $table->string("winner_user_id")->default("empty");
            $table->string("topic");
            $table->string("comment_topic");
            $table->timestamps();
        });

        Schema::table('buddhists', function (Blueprint $table) {
            $table->bigInteger("user_id")->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger("type_id")->unsigned();
            $table->foreign('type_id')->references('id')->on('types')->onDelete('cascade');

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
            $table->dropForeign(['type_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('buddhists');
    }
}
