<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueueTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('queue', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->enum('type', array('build','tech','unit'));
			$table->integer('user_id');
			$table->integer('planet_id');
			$table->integer('object_id');
			$table->integer('level');
			$table->enum('operation', array('build','destroy'));
			$table->integer('time');
			$table->integer('time_end');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('queue');
	}

}
