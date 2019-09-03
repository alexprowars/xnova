<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllianceChatTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alliance_chat', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('ally_id')->unsigned()->default(0)->index('ally_id');
			$table->string('user', 50)->default('');
			$table->integer('user_id')->unsigned()->default(0);
			$table->text('message', 65535);
			$table->integer('timestamp')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('alliance_chat');
	}

}
