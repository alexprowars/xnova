<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanetsBuildingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('planets_buildings', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('planet_id')->nullable();
			$table->integer('build_id')->nullable();
			$table->smallInteger('level')->default(0);
			$table->boolean('power')->default(10);
			$table->unique(['planet_id','build_id'], 'planet_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('planets_buildings');
	}

}
