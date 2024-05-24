<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('fleets', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->string('user_name', 50)->nullable()->default('');
			$table->integer('mission')->default(0);
			$table->bigInteger('amount')->default(0);
			$table->json('fleet_array')->nullable();
			$table->timestamp('start_time')->nullable()->index();
			$table->boolean('start_galaxy')->default(0);
			$table->smallInteger('start_system')->unsigned()->default(0);
			$table->boolean('start_planet')->default(0);
			$table->boolean('start_type')->default(0);
			$table->timestamp('end_time')->nullable()->index();
			$table->timestamp('end_stay')->nullable()->index();
			$table->boolean('end_galaxy')->default(0);
			$table->smallInteger('end_system')->unsigned()->default(0)->index();
			$table->boolean('end_planet')->default(0);
			$table->boolean('end_type')->default(0);
			$table->bigInteger('resource_metal')->unsigned()->default(0);
			$table->bigInteger('resource_crystal')->unsigned()->default(0);
			$table->bigInteger('resource_deuterium')->unsigned()->default(0);
			$table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
			$table->string('target_user_name', 50)->nullable()->default('');
			$table->foreignId('assault_id')->nullable()->constrained('assaults')->nullOnDelete();
			$table->integer('mess')->default(0);
			$table->boolean('raunds')->default(6);
			$table->boolean('won')->default(0);
			$table->timestamps();
			$table->index('updated_at');
		});
	}

	public function down()
	{
		Schema::drop('fleets');
	}
};
