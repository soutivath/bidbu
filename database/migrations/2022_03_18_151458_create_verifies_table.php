<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\VerifyStatus;
use App\Enums\VerifyFileType;
class CreateVerifiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verifies', function (Blueprint $table) {
            $table->id();
            $table->string("address")->nullable();
            $table->enum("address_verify_status",[VerifyStatus::APPROVED,VerifyStatus::PENDING,VerifyStatus::REJECTED])->nullable()->default(null);
            $table->string("phone_number")->nullable();
            $table->enum("phone_verify_status",[VerifyStatus::APPROVED,VerifyStatus::PENDING,VerifyStatus::REJECTED])->nullable()->default(null);
            $table->enum("file_type",[VerifyFileType::PASSPORT,VerifyFileType::CENCUS,VerifyFileType::IDENTITY_CARD])->nullable();
            $table->string("file_folder_path")->nullable();
            $table->enum("file_verify_status",[VerifyStatus::APPROVED,VerifyStatus::PENDING,VerifyStatus::REJECTED])->nullable()->default(null);
            $table->bigInteger("user_id")->unsigned();
            $table->timestamps();

            

        });

        Schema::table("verifies",function(Blueprint $table) {
            $table->foreign("user_id")->references("id")->on("users")->onDelete("CASCADE");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('verifies', function (Blueprint $table) {
            $table->dropForeign(["user_id"]);
          
        });
      
        Schema::dropIfExists('verifies');
    }
}
