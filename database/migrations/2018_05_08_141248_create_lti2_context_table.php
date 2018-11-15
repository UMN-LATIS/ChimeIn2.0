<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLti2ContextTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lti2_context', function(Blueprint $table)
		{
			$table->integer('context_pk', true);
			$table->integer('consumer_pk')->index('lti2_context_consumer_id_IDX');
			$table->string('lti_context_id');
			$table->text('settings', 65535)->nullable();
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
		Schema::drop('lti2_context');
	}

}
