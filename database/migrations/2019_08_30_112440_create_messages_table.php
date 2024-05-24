<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('messages', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users');
			$table->integer('from_id')->default(0)->index('message_sender');
			$table->integer('time')->default(0)->index('message_time');
			$table->integer('type')->default(0);
			$table->boolean('deleted')->default(false);
			$table->string('theme', 100)->nullable();
			$table->text('text')->nullable();
			$table->index(['user_id','deleted'], 'message_owner_2');
		});
	}

	public function down()
	{
		Schema::drop('messages');
	}
};
