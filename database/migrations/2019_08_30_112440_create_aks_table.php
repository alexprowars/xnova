<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('aks', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('name', 50)->nullable();
			$table->integer('fleet_id')->nullable();
			$table->integer('galaxy')->nullable();
			$table->integer('system')->nullable();
			$table->integer('planet')->nullable();
			$table->boolean('planet_type')->default(1);
			$table->integer('user_id')->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('aks');
	}

}
