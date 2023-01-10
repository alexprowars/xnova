<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('user_details', function (Blueprint $table) {
			$table->id();
			$table->string('password', 100)->default('');
			$table->string('email', 30);
			$table->string('name', 100)->default('');
			$table->string('second_name', 100)->default('');
			$table->string('last_name', 100)->default('');
			$table->enum('gender', array('M','F'))->nullable();
			$table->string('photo', 150)->nullable();
			$table->string('birthday', 20)->default('');
			$table->integer('create_time')->default(0);
			$table->text('fleet_shortcut');
			$table->boolean('free_race_change')->default(1);
			$table->integer('image')->default(0);
			$table->text('about');
			$table->string('username_last', 150)->default('');
			$table->json('settings');
		});
	}

	public function down()
	{
		Schema::drop('user_details');
	}
};
