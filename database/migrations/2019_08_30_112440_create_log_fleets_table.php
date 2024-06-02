<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('log_fleets', function (Blueprint $table) {
			$table->id();
			$table->tinyInteger('mission')->default(0);
			$table->tinyInteger('amount')->default(0);
			$table->foreignId('s_id')->nullable()->constrained('users')->nullOnDelete();
			$table->tinyInteger('s_galaxy')->default(0);
			$table->smallInteger('s_system')->unsigned()->default(0);
			$table->tinyInteger('s_planet')->default(0);
			$table->foreignId('e_id')->nullable()->constrained('users')->nullOnDelete();
			$table->tinyInteger('e_galaxy')->default(0);
			$table->smallInteger('e_system')->unsigned()->default(0);
			$table->tinyInteger('e_planet')->default(0);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('log_fleets');
	}
};
