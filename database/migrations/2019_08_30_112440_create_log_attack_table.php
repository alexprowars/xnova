<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogAttackTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('log_attack', function(Blueprint $table)
		{
			$table->integer('uid')->unsigned()->default(0);
			$table->integer('time')->unsigned()->default(0);
			$table->integer('planet_start')->unsigned()->default(0);
			$table->integer('planet_end')->unsigned()->default(0);
			$table->string('fleet')->default('');
			$table->integer('battle_log')->unsigned()->default(0);
			$table->index(['uid','time'], 'uid');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('log_attack');
	}

}
