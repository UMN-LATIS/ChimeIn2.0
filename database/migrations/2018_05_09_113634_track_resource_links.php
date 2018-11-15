<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrackResourceLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->integer('resource_link_pk')->nullable(); // even though this is tied to LTI, we don't foreign key it to those tables cause we don't really manage those in laravel really
            $table->foreign("resource_link_pk")->references("resource_link_pk")->on("lti2_resource_link");
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
            $table->dropForeign('folders_resource_link_pk_foreign');
            $table->dropColumn("resource_link_pk");
            
        });
    }
}
