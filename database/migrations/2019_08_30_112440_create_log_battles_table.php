<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('log_battles', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id')->index('user');
			$table->integer('time');
			$table->string('title')->default('');
			$table->text('log');
		});
	}

	public function down()
	{
		Schema::drop('log_battles');
	}
};
