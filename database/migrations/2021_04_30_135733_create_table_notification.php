<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification', function (Blueprint $table) {
            $table->id();
            $table->dateTime("notification_time");
            $table->boolean("read");
            $table->string("data");
            $table->string("notification_type");
            $table->string('comment_path');
            $table->timestamps();

        });

        Schema::table('notification', function (Blueprint $table) {
            $table->bigInteger('buddhist_id')->unsigned();
            $table->foreign('buddhist_id')->references('id')->on('buddhists')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');



        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification', function (Blueprint $table) {
         //   $table->dropForeign(['buddhist_id']);
            $table->dropForeign(['user_id']);

        });
        Schema::dropIfExists('notification');
    }
}
