<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCanvasGradeSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chimes', function (Blueprint $table) {
            $table->dropColumn("single_chime_for_lti");
            $table->string("lti_grade_mode")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chimes', function (Blueprint $table) {
            $table->dropColumn("lti_grade_mode");
            $table->boolean("single_chime_for_lti")->default(0);
        });
    }
}
