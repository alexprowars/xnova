<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogHistoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('log_history', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('user_id')->index('user_id');
			$table->integer('time');
			$table->integer('planet');
			$table->integer('operation');
			$table->integer('from_metal');
			$table->integer('from_crystal');
			$table->integer('from_deuterium');
			$table->integer('to_metal');
			$table->integer('to_crystal');
			$table->integer('to_deuterium');
			$table->integer('build_id');
			$table->integer('tech_id');
			$table->integer('level');
			$table->integer('count');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('log_history');
	}

}
