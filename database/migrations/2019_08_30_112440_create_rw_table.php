<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRwTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rw', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('id_users')->default('');
			$table->text('raport', 16777215);
			$table->boolean('no_contact')->default(0);
			$table->integer('time')->unsigned()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('rw');
	}

}
