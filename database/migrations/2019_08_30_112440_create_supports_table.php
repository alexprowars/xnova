<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('supports', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id')->index('player_id');
			$table->integer('time')->default(0);
			$table->string('subject')->default('');
			$table->text('text');
			$table->integer('status')->default(1);
		});
	}

	public function down()
	{
		Schema::drop('supports');
	}
};
