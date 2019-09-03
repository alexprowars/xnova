<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrivateTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('private', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('u_id')->default(0)->index('u_id');
			$table->integer('a_id')->default(0);
			$table->string('text');
			$table->integer('time')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('private');
	}

}
