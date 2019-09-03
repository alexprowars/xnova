<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('modules', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('code', 50)->default('');
			$table->smallInteger('sort')->default(0);
			$table->string('namespace', 100)->default('');
			$table->enum('active', array('N','Y'))->default('N');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('modules');
	}

}
