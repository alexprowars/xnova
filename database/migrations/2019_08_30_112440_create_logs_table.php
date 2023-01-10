<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('logs', function (Blueprint $table) {
			$table->boolean('mission')->default(0);
			$table->integer('time')->unsigned()->default(0)->index('time');
			$table->boolean('kolvo')->default(1);
			$table->integer('s_id')->unsigned()->default(0)->index('s_id');
			$table->boolean('s_galaxy')->default(0);
			$table->smallInteger('s_system')->unsigned()->default(0);
			$table->boolean('s_planet')->default(0);
			$table->integer('e_id')->unsigned()->default(0);
			$table->boolean('e_galaxy')->default(0);
			$table->smallInteger('e_system')->unsigned()->default(0);
			$table->boolean('e_planet')->default(0);
		});
	}

	public function down()
	{
		Schema::drop('logs');
	}
};
