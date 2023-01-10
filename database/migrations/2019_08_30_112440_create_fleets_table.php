<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('fleets', function (Blueprint $table) {
			$table->id();
			$table->integer('owner')->default(0)->index('fleet_owner');
			$table->string('owner_name', 35)->default('');
			$table->integer('mission')->default(0);
			$table->bigInteger('amount')->default(0);
			$table->json('fleet_array')->nullable();
			$table->integer('start_time')->default(0)->index('fleet_start_time');
			$table->boolean('start_galaxy')->default(0);
			$table->smallInteger('start_system')->unsigned()->default(0);
			$table->boolean('start_planet')->default(0);
			$table->boolean('start_type')->default(0);
			$table->integer('end_time')->default(0)->index('fleet_end_time');
			$table->integer('end_stay')->default(0)->index('fleet_end_stay');
			$table->boolean('end_galaxy')->default(0);
			$table->smallInteger('end_system')->unsigned()->default(0)->index('fleet_end_system');
			$table->boolean('end_planet')->default(0);
			$table->boolean('end_type')->default(0);
			$table->bigInteger('resource_metal')->unsigned()->default(0);
			$table->bigInteger('resource_crystal')->unsigned()->default(0);
			$table->bigInteger('resource_deuterium')->unsigned()->default(0);
			$table->integer('target_owner')->default(0)->index('fleet_target_owner');
			$table->string('target_owner_name', 35)->default('');
			$table->integer('group_id')->default(0)->index('fleet_group');
			$table->integer('mess')->default(0);
			$table->integer('create_time')->default(0);
			$table->integer('update_time')->default(0)->index('fleet_time');
			$table->boolean('raunds')->default(6);
			$table->boolean('won')->default(0);
		});
	}

	public function down()
	{
		Schema::drop('fleets');
	}
};
