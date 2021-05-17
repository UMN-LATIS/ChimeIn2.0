<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FolderTrackLineItemInsteadOfResourceLink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropForeign("folders_lti13_resource_link_id_foreign");
            $table->dropColumn("lti13_resource_link_id");
            $table->string("lti_lineitem")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn("lti_lineitem");
            $table->biginteger("lti13_resource_link_id")->unsigned()->nullable();
            $table->foreign("lti13_resource_link_id")->references("id")->on("lti13_resource_links")->onDelete("cascade");
        });
    }
}
