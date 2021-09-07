<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChatRoom extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_room', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger("buddhist_id")->unsigned();
            $table->bigInteger("user_1")->unsigned();
            $table->bigInteger("user_2")->unsigned();
            $table->timestamps();
        });

        Schema::table('chat_room', function (Blueprint $table) {

            $table->foreign("buddhist_id")->references("id")->on("buddhists")->onDelete("cascade");
            $table->foreign("user_1")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("user_2")->references("id")->on("users")->onDelete("cascade");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('chat_room', function (Blueprint $table) {
            $table->dropForeign(['buddhist_id']);
            $table->dropForeign(['user_1']);
            $table->dropForeign(['user_2']);
        });
        Schema::dropIfExists('chat_room');

    }
}
