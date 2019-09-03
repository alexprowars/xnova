<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllianceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alliance', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name', 32);
			$table->string('tag', 8);
			$table->integer('owner')->default(0);
			$table->integer('create_time')->default(0);
			$table->text('description', 65535)->nullable();
			$table->string('web')->nullable();
			$table->text('text', 65535)->nullable();
			$table->integer('image')->default(0);
			$table->text('request', 65535)->nullable();
			$table->boolean('request_notallow')->default(0);
			$table->string('owner_range', 32)->nullable();
			$table->text('ranks', 65535)->nullable();
			$table->boolean('members')->default(1);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('alliance');
	}

}
