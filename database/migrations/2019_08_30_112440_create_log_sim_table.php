<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogSimTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('log_sim', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('sid', 50)->default('')->unique('sid');
			$table->integer('time')->default(0);
			$table->text('data', 65535);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('log_sim');
	}

}
