<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->string('name')->default('guest');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->integer('permission_number')->unsigned()->default(100);
            $table->rememberToken();
            $table->timestamps();
            $table->string('umndid')->nullable();
            $table->string('userType')->nullable();
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
