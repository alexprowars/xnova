<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtifactsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('artifacts', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('user_id')->default(0)->index('user_id');
			$table->smallInteger('element_id')->default(0);
			$table->boolean('level')->default(1);
			$table->integer('expired')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('artifacts');
	}

}
