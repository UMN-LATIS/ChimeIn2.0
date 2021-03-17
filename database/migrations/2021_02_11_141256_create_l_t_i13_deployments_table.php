<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLTI13DeploymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lti13_deployments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("deployment_id");
            $table->biginteger("issuer_id")->unsigned();
            $table->foreign("issuer_id")->references("id")->on("lti13_issuers")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lti13_deployments');
    }
}
