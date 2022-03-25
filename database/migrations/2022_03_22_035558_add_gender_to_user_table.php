<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\GenderEnum;
class AddGenderToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum("gender",[GenderEnum::MALE,GenderEnum::FEMALE])->nullable();
            $table->date("date_of_birth")->nullable();
            $table->string("emergency_phone_number")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(["gender","date_of_birth","emergency_phone_number"]);
        });

       
    }
}
