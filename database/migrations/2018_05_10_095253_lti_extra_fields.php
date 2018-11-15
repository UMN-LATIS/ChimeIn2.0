<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LtiExtraFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chimes', function (Blueprint $table) {
            $table->string('lti_return_url')->nullable(); 
            $table->string('lti_course_title')->nullable(); 
            $table->string('lti_course_id')->nullable(); 
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
            $table->dropColumn('lti_return_url');
            $table->dropColumn("lti_course_title");
            $table->dropColumn("lti_course_id");
        });
    }
}
