<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogRefersTable extends Migration
{
	public function up()
	{
		Schema::create('log_refers', function (Blueprint $table) {
			$table->integer('time')->default(0);
			$table->integer('user_id')->default(0);
			$table->smallInteger('refers')->default(0);
		});
	}

	public function down()
	{
		Schema::drop('log_refers');
	}
}
