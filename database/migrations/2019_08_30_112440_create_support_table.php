<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('support', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('user_id')->default(9)->index('player_id');
			$table->integer('time')->default(0);
			$table->string('subject')->default('');
			$table->text('text', 65535);
			$table->integer('status')->default(1);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('support');
	}

}
