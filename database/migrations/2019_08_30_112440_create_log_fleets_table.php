<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('log_fleets', function (Blueprint $table) {
			$table->id();
			$table->boolean('mission')->default(0);
			$table->boolean('amount')->default(0);
			$table->foreignId('s_id')->nullable()->constrained('users')->nullOnDelete();
			$table->boolean('s_galaxy')->default(0);
			$table->smallInteger('s_system')->unsigned()->default(0);
			$table->boolean('s_planet')->default(0);
			$table->foreignId('e_id')->nullable()->constrained('users')->nullOnDelete();
			$table->boolean('e_galaxy')->default(0);
			$table->smallInteger('e_system')->unsigned()->default(0);
			$table->boolean('e_planet')->default(0);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('log_fleets');
	}
};
