<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('fleets', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->string('user_name', 50)->nullable()->default('');
			$table->integer('mission')->nullable();
			$table->bigInteger('amount')->default(0);
			$table->json('entities')->default(new Expression('(JSON_ARRAY())'));
			$table->timestamp('start_date')->nullable()->index();
			$table->tinyInteger('start_galaxy')->nullable();
			$table->smallInteger('start_system')->nullable();
			$table->tinyInteger('start_planet')->nullable();
			$table->tinyInteger('start_type')->nullable();
			$table->timestamp('end_date')->nullable()->index();
			$table->timestamp('end_stay')->nullable()->index();
			$table->tinyInteger('end_galaxy')->nullable();
			$table->smallInteger('end_system')->nullable()->index();
			$table->tinyInteger('end_planet')->nullable();
			$table->tinyInteger('end_type')->nullable();
			$table->bigInteger('resource_metal')->unsigned()->default(0);
			$table->bigInteger('resource_crystal')->unsigned()->default(0);
			$table->bigInteger('resource_deuterium')->unsigned()->default(0);
			$table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
			$table->string('target_user_name', 50)->nullable()->default('');
			$table->foreignId('assault_id')->nullable()->constrained('assaults')->nullOnDelete();
			$table->integer('mess')->default(0);
			$table->tinyInteger('rounds')->default(6);
			$table->tinyInteger('won')->default(0);
			$table->timestamps();
			$table->index('updated_at');
		});
	}

	public function down()
	{
		Schema::drop('fleets');
	}
};
