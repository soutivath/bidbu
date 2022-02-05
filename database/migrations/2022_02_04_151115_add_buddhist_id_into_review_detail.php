<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBuddhistIdIntoReviewDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('review_details', function (Blueprint $table) {
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
        Schema::table('review_details', function (Blueprint $table) {
            $table->dropForeign(["buddhist_id"]);
          
        });

      

        
    }
}
