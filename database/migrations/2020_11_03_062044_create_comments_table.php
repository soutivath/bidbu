<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('message');
            $table->timestamps();
        });
        Schema::table('comments',function(Blueprint $table)
        {
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('buddhist_id')->unsigned();
            $table->foreign('buddhist_id')->references('id')->on('buddhists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("comments",function(Blueprint $table)
        {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['buddhist_id']);
        });
        Schema::dropIfExists('comments');
    }
}
