<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGlobalLineitemToResourcelink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lti13_resource_links', function (Blueprint $table) {
            $table->string("created_line_item")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lti13_resource_links', function (Blueprint $table) {
            $table->dropColumn("created_line_item");
        });
    }
}
