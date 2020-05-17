<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogCreditsTable extends Migration
{
	public function up()
	{
		Schema::create('log_credits', function (Blueprint $table) {
			$table->integer('uid')->default(0);
			$table->integer('time')->default(0);
			$table->smallInteger('credits')->default(0);
			$table->boolean('type')->default(0);
		});
	}

	public function down()
	{
		Schema::drop('log_credits');
	}
}
