<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sessions', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('token', 100)->nullable()->unique('token');
			$table->enum('object_type', array('client','user'))->nullable();
			$table->integer('object_id')->nullable();
			$table->integer('timestamp')->nullable();
			$table->integer('lifetime')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sessions');
	}

}
