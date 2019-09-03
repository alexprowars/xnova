<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHallTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('hall', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('title', 150);
			$table->integer('debris');
			$table->integer('time')->index('time');
			$table->boolean('won');
			$table->boolean('sab')->default(0)->index('sab');
			$table->integer('log');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('hall');
	}

}
