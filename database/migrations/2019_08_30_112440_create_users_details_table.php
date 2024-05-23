<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('users_details', function (Blueprint $table) {
			$table->id();
			$table->boolean('free_race_change')->default(1);
			$table->integer('image')->default(0);
			$table->text('about');
			$table->string('username_last', 150)->default('');
		});
	}

	public function down()
	{
		Schema::drop('users_details');
	}
};
