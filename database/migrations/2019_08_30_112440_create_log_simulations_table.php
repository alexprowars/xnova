<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogSimulationsTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('log_simulations', function (Blueprint $table) {
			$table->integer('id', true);
			$table->string('sid', 50)->default('')->unique('sid');
			$table->integer('time')->default(0);
			$table->text('data');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('log_simulations');
	}
}
