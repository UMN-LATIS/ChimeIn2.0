<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class KeyForResponses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("responses", function(Blueprint $table) {
            $table->foreign("session_id")->references("id")->on("sessions");
            $table->foreign("user_id")->references("id")->on("users");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("responses", function(Blueprint $table) {
            $table->dropForeign("responses_session_id_foreign");
            $table->dropForeign("responses_user_id_foreign");
        });

    }
}
