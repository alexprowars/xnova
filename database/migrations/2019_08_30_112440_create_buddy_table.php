<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuddyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('buddy', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('sender')->default(0)->index('sender');
			$table->integer('owner')->default(0)->index('owner');
			$table->boolean('ignor')->default(0);
			$table->boolean('active')->default(0);
			$table->string('text', 250);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('buddy');
	}

}
