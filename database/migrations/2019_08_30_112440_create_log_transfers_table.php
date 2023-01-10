<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('log_transfers', function (Blueprint $table) {
			$table->id();
			$table->integer('time')->default(0);
			$table->unsignedBigInteger('user_id')->index('user_id');
			$table->text('data');
			$table->integer('target_id')->default(0);
		});
	}

	public function down()
	{
		Schema::drop('log_transfers');
	}
};
