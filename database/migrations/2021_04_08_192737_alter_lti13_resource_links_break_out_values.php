<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterLti13ResourceLinksBreakOutValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("lti13_resource_links", function(Blueprint $table) {
            $table->dropColumn("launch_data");
            $table->json("endpoint");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("lti13_resource_links", function(Blueprint $table) {
            $table->dropColumn("endpoint");
            $table->json("launch_data");
        });
    }
}
