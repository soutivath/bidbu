<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShowBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('show_banners', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("language_id")->unsigned();
            $table->timestamps();
        });
        Schema::table("show_banners",function (Blueprint $table){
            $table->foreign("language_id")->references("languages")->on("id")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("show_banners",function (Blueprint $table){
            $table->dropForeign(["language_id"]);
        });
        Schema::dropIfExists('show_banners');
    }
}
