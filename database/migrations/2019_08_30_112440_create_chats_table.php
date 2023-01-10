<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('chats', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id');
			$table->text('text');
			$table->timestamps();

			$table->foreign('user_id')
				->references('id')
				->on('users')
				->onDelete('cascade');
		});
	}

	public function down()
	{
		Schema::drop('chats');
	}
};
