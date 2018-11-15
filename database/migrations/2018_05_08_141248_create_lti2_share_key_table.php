<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLti2ShareKeyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lti2_share_key', function(Blueprint $table)
		{
			$table->string('share_key_id', 32)->primary();
			$table->integer('resource_link_pk')->index('lti2_share_key_resource_link_pk_IDX');
			$table->boolean('auto_approve');
			$table->dateTime('expires');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('lti2_share_key');
	}

}
