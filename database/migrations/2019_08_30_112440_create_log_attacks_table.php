<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('log_attacks', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->integer('planet_start')->unsigned()->default(0);
			$table->integer('planet_end')->unsigned()->default(0);
			$table->json('fleet')->nullable();
			$table->integer('battle_log')->unsigned()->default(0);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('log_attacks');
	}
};
