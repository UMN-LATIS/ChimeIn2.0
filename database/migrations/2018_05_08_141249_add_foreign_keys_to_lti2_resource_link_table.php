<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToLti2ResourceLinkTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('lti2_resource_link', function(Blueprint $table)
		{
			$table->foreign('context_pk', 'lti2_resource_link_lti2_context_FK1')->references('context_pk')->on('lti2_context')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('primary_resource_link_pk', 'lti2_resource_link_lti2_resource_link_FK1')->references('resource_link_pk')->on('lti2_resource_link')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('lti2_resource_link', function(Blueprint $table)
		{
			$table->dropForeign('lti2_resource_link_lti2_context_FK1');
			$table->dropForeign('lti2_resource_link_lti2_resource_link_FK1');
		});
	}

}
