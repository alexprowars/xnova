<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSavelogTable extends Migration
{
	public function up()
	{
		Schema::create('log_battles', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('user_id')->default(0)->index('user');
			$table->integer('time');
			$table->string('title')->default('');
			$table->text('log');
		});
	}

	public function down()
	{
		Schema::drop('log_battles');
	}
}
