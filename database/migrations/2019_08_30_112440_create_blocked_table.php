<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockedTable extends Migration
{
	public function up()
	{
		Schema::create('blocked', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('who')->default(0);
			$table->text('theme');
			$table->integer('time')->default(0);
			$table->integer('longer')->default(0);
			$table->integer('author')->default(0);
		});
	}

	public function down()
	{
		Schema::drop('blocked');
	}
}
