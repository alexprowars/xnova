<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogChatTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('log_chat', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('time')->default(0);
			$table->integer('user')->default(0);
			$table->string('user_name', 100)->default('');
			$table->text('text', 65535);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('log_chat');
	}

}
