<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLti2UserResultTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lti2_user_result', function(Blueprint $table)
		{
			$table->integer('user_pk', true);
			$table->integer('resource_link_pk')->index('lti2_user_result_resource_link_pk_IDX');
			$table->string('lti_user_id');
			$table->string('lti_result_sourcedid', 1024);
			$table->dateTime('created');
			$table->dateTime('updated');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('lti2_user_result');
	}

}
