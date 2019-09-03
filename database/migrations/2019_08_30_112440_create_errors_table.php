<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErrorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('errors', function(Blueprint $table)
		{
			$table->bigInteger('error_id', true);
			$table->string('error_sender', 32)->default('0');
			$table->integer('error_time')->default(0);
			$table->string('error_type', 32)->default('unknown');
			$table->text('error_text', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('errors');
	}

}
