<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllianceChatsTable extends Migration
{
	public function up()
	{
		Schema::create('alliance_chats', function (Blueprint $table) {
			$table->id();
			$table->integer('ally_id')->unsigned()->default(0)->index('ally_id');
			$table->string('user', 50)->default('');
			$table->integer('user_id')->unsigned()->default(0);
			$table->text('message');
			$table->integer('timestamp')->default(0);
		});
	}

	public function down()
	{
		Schema::drop('alliance_chats');
	}
}
