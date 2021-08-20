<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecommendedBuddhistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recommended_buddhists', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("buddhist_id")->unsigned();
            $table->foreign("buddhist_id")->references("id")->on("buddhists")->onDelete("cascade");
            $table->timestamps();
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
            $table->dropForeign(['buddhist_id']);
        });
        Schema::dropIfExists('recommended_buddhists');
    }
}
