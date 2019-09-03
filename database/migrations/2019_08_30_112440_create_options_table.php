<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('options', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name', 200)->default('')->unique('name');
			$table->string('title', 250)->default('');
			$table->text('value', 65535)->nullable();
			$table->enum('group_id', array('general','xnova'))->default('general');
			$table->enum('type', array('string','integer','text','checkbox'))->default('string');
			$table->string('def', 250);
			$table->string('description', 250);
			$table->enum('cached', array('N','Y'))->default('Y');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('options');
	}

}
