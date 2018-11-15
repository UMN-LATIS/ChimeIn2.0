<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToLti2UserResultTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('lti2_user_result', function(Blueprint $table)
		{
			$table->foreign('resource_link_pk', 'lti2_user_result_lti2_resource_link_FK1')->references('resource_link_pk')->on('lti2_resource_link')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('lti2_user_result', function(Blueprint $table)
		{
			$table->dropForeign('lti2_user_result_lti2_resource_link_FK1');
		});
	}

}
