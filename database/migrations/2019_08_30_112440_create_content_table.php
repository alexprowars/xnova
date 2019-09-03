<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('content', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('title', 150)->default('');
			$table->string('alias', 100)->default('')->unique('alias');
			$table->text('html', 65535);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('content');
	}

}
