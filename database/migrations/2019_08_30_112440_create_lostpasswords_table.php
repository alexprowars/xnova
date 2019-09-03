<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLostpasswordsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lostpasswords', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('user_id')->default(0);
			$table->string('keystring', 50)->default('');
			$table->integer('time')->default(0);
			$table->string('ip', 30)->default('');
			$table->enum('active', array('0','1'));
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('lostpasswords');
	}

}
