<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllianceDiplomacyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alliance_diplomacy', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('a_id')->default(0);
			$table->integer('d_id')->default(0);
			$table->boolean('type')->default(0);
			$table->boolean('status')->default(0);
			$table->boolean('primary')->default(0);
			$table->index(['a_id','d_id'], 'a_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('alliance_diplomacy');
	}

}
