<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogTransfersTable extends Migration
{
	public function up()
	{
		Schema::create('log_transfers', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('time')->default(0);
			$table->integer('user_id')->default(0)->index('user_id');
			$table->text('data');
			$table->integer('target_id')->default(0);
		});
	}

	public function down()
	{
		Schema::drop('log_transfers');
	}
}
