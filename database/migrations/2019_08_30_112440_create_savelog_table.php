<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSavelogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('savelog', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->default(0)->index('user');
			$table->integer('time');
			$table->string('title')->default('');
			$table->text('log', 16777215);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('savelog');
	}

}
