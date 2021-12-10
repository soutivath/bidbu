<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannerTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banner_trans', function (Blueprint $table) {
            $table->id();
            $table->string("image_path");
            $table->bigInteger("banner_id")->unsigned();
            $table->bigInteger("language_id")->unsigned();
            $table->timestamps();
        });

        Schema::table("banner_trans",function (Blueprint $table)
        {
            $table->foreign("banner_id")->references("id")->on("banners")->onDelete("CASCADE");
            $table->foreign("language_id")->references("id")->on("languages")->onDelete("CASCADE");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("banner_trans",function (Blueprint $table){
            $table->dropForeign(["banner_id"]);
            $table->dropForeign(["language_id"]);
        });
        Schema::dropIfExists('banner_trans');
    }
}
