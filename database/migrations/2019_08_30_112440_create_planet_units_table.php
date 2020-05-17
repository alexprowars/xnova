<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanetUnitsTable extends Migration
{
	public function up()
	{
		Schema::create('planet_units', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('planet_id')->nullable();
			$table->integer('unit_id')->nullable();
			$table->integer('amount')->default(0);
			$table->boolean('power')->default(10);
			$table->unique(['planet_id','unit_id'], 'planet_id');
		});
	}

	public function down()
	{
		Schema::drop('planet_units');
	}
}
