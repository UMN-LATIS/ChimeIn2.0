<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign("question_id")->references("id")->on("questions");
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->foreign("folder_id")->references("id")->on("folders");
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->foreign("chime_id")->references("id")->on("chimes");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sessions', function (Blueprint $table) { 
            $table->dropForeign("sessions_question_id_foreign");
        });
        Schema::table('questions', function (Blueprint $table) { 
            $table->dropForeign("questions_folder_id_foreign");
        });
        Schema::table('folders', function (Blueprint $table) { 
            $table->dropForeign("folders_chime_id_foreign");
        });
    }
}
