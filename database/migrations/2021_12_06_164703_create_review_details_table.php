<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("review_id")->unsigned();
            $table->bigInteger("user_id")->unsigned();
            $table->unsignedTinyInteger("score");
            $table->string("comment");
            $table->timestamps();
        });
        Schema::table("review_details",function (Blueprint $table){
            $table->foreign("review_id")->references("id")->on("reviews")->onDelete("cascade");
            $table->foreign("user_id")->references("id")->on("uers")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("review_details",function (Blueprint $table){
            $table->dropForeign(["review_id","user_id"]);
        });
        Schema::dropIfExists('review_details');
    }
}
