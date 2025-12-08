<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('logs_histories', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->integer('planet');
			$table->integer('operation');
			$table->integer('from_metal');
			$table->integer('from_crystal');
			$table->integer('from_deuterium');
			$table->integer('to_metal');
			$table->integer('to_crystal');
			$table->integer('to_deuterium');
			$table->integer('entity_id');
			$table->integer('amount');
			$table->timestamp('created_at')->useCurrent();
		});
	}

	public function down()
	{
		Schema::drop('logs_histories');
	}
};
