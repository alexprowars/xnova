<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('artifacts', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users');
			$table->smallInteger('element_id')->default(0);
			$table->tinyInteger('level')->default(1);
			$table->integer('expired')->default(0);
		});
	}

	public function down()
	{
		Schema::drop('artifacts');
	}
};
