<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogChatsTable extends Migration
{
	public function up()
	{
		Schema::create('log_chats', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('time')->default(0);
			$table->integer('user')->default(0);
			$table->string('user_name', 100)->default('');
			$table->text('text');
		});
	}

	public function down()
	{
		Schema::drop('log_chats');
	}
}
