<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("questions", function(Blueprint $table) {
            $table->dropForeign("questions_current_session_foreign");
            $table->renameColumn('current_session', 'current_session_id');
            $table->foreign("current_session_id")->references("id")->on("sessions");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("questions", function(Blueprint $table) {
            $table->dropForeign("questions_current_session_id_foreign");
            $table->renameColumn('current_session_id', 'current_session');
            $table->foreign("current_session")->references("id")->on("sessions");
        });
    }
}
