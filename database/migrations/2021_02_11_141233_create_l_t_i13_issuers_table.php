<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLTI13IssuersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lti13_issuers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("host");
            $table->string("client_id");
            $table->string("auth_login_url");
            $table->string("auth_token_url");
            $table->string("key_set_url");
            $table->text("private_key");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lti13_issuers');
    }
}
