<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannedTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('banned', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('who')->default(0);
			$table->text('theme', 65535);
			$table->integer('time')->default(0);
			$table->integer('longer')->default(0);
			$table->integer('author')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('banned');
	}

}
