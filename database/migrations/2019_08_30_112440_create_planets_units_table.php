<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanetsUnitsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('planets_units', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('planet_id')->nullable();
			$table->integer('unit_id')->nullable();
			$table->integer('amount')->default(0);
			$table->boolean('power')->default(10);
			$table->unique(['planet_id','unit_id'], 'planet_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('planets_units');
	}

}
