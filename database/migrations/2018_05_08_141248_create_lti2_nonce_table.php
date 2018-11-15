<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLti2NonceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lti2_nonce', function(Blueprint $table)
		{
			$table->integer('consumer_pk');
			$table->string('value', 64)->default('');
			$table->dateTime('expires');
			$table->primary(['consumer_pk','value']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('lti2_nonce');
	}

}
