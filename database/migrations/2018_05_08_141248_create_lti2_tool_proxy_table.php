<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLti2ToolProxyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lti2_tool_proxy', function(Blueprint $table)
		{
			$table->integer('tool_proxy_pk', true);
			$table->string('tool_proxy_id', 32)->unique('lti2_tool_proxy_tool_proxy_id_UNIQUE');
			$table->integer('consumer_pk')->index('lti2_tool_proxy_consumer_id_IDX');
			$table->text('tool_proxy', 65535);
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
		Schema::drop('lti2_tool_proxy');
	}

}
