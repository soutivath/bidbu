<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            //  $table->string('email')->unique(); // should delete
            $table->string('phone_number')->unique();
            $table->string('firebase_uid')->nullable()->unique();
            $table->string('password');
            $table->string('picture');
            $table->timestamp('email_verified_at')->nullable();
            $table->string("topic");
            $table->string("active")->default("1");
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
