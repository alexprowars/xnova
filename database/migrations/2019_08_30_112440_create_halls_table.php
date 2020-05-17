<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHallsTable extends Migration
{
	public function up()
	{
		Schema::create('halls', function (Blueprint $table) {
			$table->integer('id', true);
			$table->string('title', 150);
			$table->integer('debris');
			$table->integer('time')->index('time');
			$table->boolean('won');
			$table->boolean('sab')->default(0)->index('sab');
			$table->integer('log');
		});
	}

	public function down()
	{
		Schema::drop('halls');
	}
}
