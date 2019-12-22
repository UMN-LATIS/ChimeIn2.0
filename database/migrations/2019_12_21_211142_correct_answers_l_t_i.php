<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CorrectAnswersLTI extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chimes', function(Blueprint $table) {
            $table->boolean('only_correct_answers_lti')->default(false);
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chimes', function(Blueprint $table) {
            $table->dropColumn('only_correct_answers_lti');
        });
    }
}
