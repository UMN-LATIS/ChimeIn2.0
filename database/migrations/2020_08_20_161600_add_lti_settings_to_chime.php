<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLtiSettingsToChime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chimes', function (Blueprint $table) {
            $table->boolean("single_chime_for_lti")->default(false);
            $table->boolean("lti_setup_complete")->default(false);
            $table->integer("resource_link_pk")->nullable();
            $table->foreign("resource_link_pk")->references("resource_link_pk")->on("lti2_resource_link");
        });
        DB::table("chimes")->update(array('lti_setup_complete' => 1));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chimes', function (Blueprint $table) {
            $table->dropColumn("single_chime_for_lti");
            $table->dropColumn("lti_setup_complete");
            $table->dropColumn("resource_link_pk");
            $table->dropForeign('chimes_resource_link_pk_foreign');
        });
    }
}
