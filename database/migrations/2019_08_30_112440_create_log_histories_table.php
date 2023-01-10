<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('log_histories', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id')->index('user_id');
			$table->integer('time');
			$table->integer('planet');
			$table->integer('operation');
			$table->integer('from_metal');
			$table->integer('from_crystal');
			$table->integer('from_deuterium');
			$table->integer('to_metal');
			$table->integer('to_crystal');
			$table->integer('to_deuterium');
			$table->integer('build_id');
			$table->integer('tech_id');
			$table->integer('level');
			$table->integer('count');
		});
	}

	public function down()
	{
		Schema::drop('log_histories');
	}
};
