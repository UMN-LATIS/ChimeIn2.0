<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Lti13ResourceLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lti13_resource_links', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("resource_link");
            $table->biginteger("deployment_id")->unsigned()->nullable();
            $table->foreign("deployment_id")->references("id")->on("lti13_deployments")->onDelete("set null");
            $table->json("launch_data");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lti13_resource_links');
    }
}
