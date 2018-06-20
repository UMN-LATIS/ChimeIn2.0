<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CurrentSessionFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->integer("current_session")->unsigned()->nullable();
            $table->foreign('current_session')->references('id')->on('sessions');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn("in_progress");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {

            $table->dropForeign("current_session_sessions_id_foreign");
            $table->dropColumn("current_session");
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->boolean("in_progress")->default(false);
        });
    }
}
