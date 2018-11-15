<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLti2ConsumerTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lti2_consumer', function(Blueprint $table)
		{
			$table->integer('consumer_pk', true);
			$table->string('name', 50);
			$table->string('consumer_key256', 256);
			$table->text('consumer_key', 65535)->nullable();
			$table->string('secret', 1024);
			$table->string('lti_version', 10)->nullable();
			$table->string('consumer_name')->nullable();
			$table->string('consumer_version')->nullable();
			$table->string('consumer_guid', 1024)->nullable();
			$table->text('profile', 65535)->nullable();
			$table->text('tool_proxy', 65535)->nullable();
			$table->text('settings', 65535)->nullable();
			$table->boolean('protected');
			$table->boolean('enabled');
			$table->dateTime('enable_from')->nullable();
			$table->dateTime('enable_until')->nullable();
			$table->date('last_access')->nullable();
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
		Schema::drop('lti2_consumer');
	}

}
