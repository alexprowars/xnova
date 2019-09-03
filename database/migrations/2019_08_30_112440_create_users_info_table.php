<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersInfoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users_info', function(Blueprint $table)
		{
			$table->integer('id')->unsigned()->default(0)->primary();
			$table->char('password', 32)->default('');
			$table->string('email', 30);
			$table->string('name', 100)->default('');
			$table->string('second_name', 100)->default('');
			$table->string('last_name', 100)->default('');
			$table->enum('gender', array('M','F'))->nullable();
			$table->string('photo', 150)->nullable();
			$table->string('birthday', 20)->default('');
			$table->integer('create_time')->default(0);
			$table->text('fleet_shortcut', 65535);
			$table->boolean('free_race_change')->default(1);
			$table->integer('image')->default(0);
			$table->text('about', 65535);
			$table->string('username_last', 150)->default('');
			$table->json('settings');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users_info');
	}

}
