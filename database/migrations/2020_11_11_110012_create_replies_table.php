<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('message');
        });
        Schema::table('replies',function(Blueprint $table)
        {
            $table->bigInteger("user_id")->unsigned();
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
            $table->bigInteger("comment_id")->unsigned();
            $table->foreign("comment_id")->references("id")->on("comments")->onDelete("cascade");
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('replies',function(Blueprint $table)
        {
            $table->dropForeign(["user_id"]);
            $table->dropForeign(["comment_id"]);
        });
        Schema::dropIfExists('replies');
        
    }
}
