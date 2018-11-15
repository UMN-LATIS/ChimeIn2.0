<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLti2ResourceLinkTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lti2_resource_link', function(Blueprint $table)
		{
			$table->integer('resource_link_pk', true);
			$table->integer('context_pk')->nullable()->index('lti2_resource_link_context_pk_IDX');
			$table->integer('consumer_pk')->nullable()->index('lti2_resource_link_consumer_pk_IDX');
			$table->string('lti_resource_link_id');
			$table->text('settings', 65535)->nullable();
			$table->integer('primary_resource_link_pk')->nullable()->index('lti2_resource_link_lti2_resource_link_FK1');
			$table->boolean('share_approved')->nullable();
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
		Schema::drop('lti2_resource_link');
	}

}
