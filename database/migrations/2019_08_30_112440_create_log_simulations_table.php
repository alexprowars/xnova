<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('log_simulations', function (Blueprint $table) {
			$table->id();
			$table->string('sid', 50)->default('')->unique('sid');
			$table->integer('time')->default(0);
			$table->text('data');
		});
	}

	public function down()
	{
		Schema::drop('log_simulations');
	}
};
