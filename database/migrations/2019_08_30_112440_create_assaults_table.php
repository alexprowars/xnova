<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('assaults', function (Blueprint $table) {
			$table->id();
			$table->string('name', 100)->nullable();
			$table->integer('fleet_id')->nullable();
			$table->integer('galaxy')->nullable();
			$table->integer('system')->nullable();
			$table->integer('planet')->nullable();
			$table->tinyInteger('planet_type')->default(1);
			$table->foreignId('user_id')->constrained('users');
		});
	}

	public function down()
	{
		Schema::drop('assaults');
	}
};
