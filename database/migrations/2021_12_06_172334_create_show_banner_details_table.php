<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShowBannerDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('show_banner_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("banner_id")->unsigned();
            $table->timestamps();
        });
        Schema::table("show_banner_details",function (Blueprint $table){
            $table->foreign("banner_id")->references("banner")->on("id")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("show_banner_details",function (Blueprint $table){
            $table->dropForeign(["banner_id"]);
        });
        Schema::dropIfExists('show_banner_details');
    }
}
