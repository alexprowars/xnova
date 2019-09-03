<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogStatsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('log_stats', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('object_id')->default(0);
			$table->boolean('type')->default(0);
			$table->integer('time')->default(0);
			$table->integer('tech_rank')->default(0);
			$table->integer('tech_points')->default(0);
			$table->integer('build_rank')->default(0);
			$table->integer('build_points')->default(0);
			$table->integer('defs_rank')->default(0);
			$table->integer('defs_points')->default(0);
			$table->integer('fleet_rank')->default(0);
			$table->integer('fleet_points')->default(0);
			$table->integer('total_rank')->default(0);
			$table->integer('total_points')->default(0);
			$table->index(['object_id','type'], 'id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('log_stats');
	}

}
