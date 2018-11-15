<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToLti2NonceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('lti2_nonce', function(Blueprint $table)
		{
			$table->foreign('consumer_pk', 'lti2_nonce_lti2_consumer_FK1')->references('consumer_pk')->on('lti2_consumer')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('lti2_nonce', function(Blueprint $table)
		{
			$table->dropForeign('lti2_nonce_lti2_consumer_FK1');
		});
	}

}
