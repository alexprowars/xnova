<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllianceDiplomaciesTable extends Migration
{
	public function up()
	{
		Schema::create('alliance_diplomacies', function (Blueprint $table) {
			$table->id();
			$table->integer('a_id')->default(0);
			$table->integer('d_id')->default(0);
			$table->boolean('type')->default(0);
			$table->boolean('status')->default(0);
			$table->boolean('primary')->default(0);
			$table->index(['a_id','d_id'], 'a_id');
		});
	}

	public function down()
	{
		Schema::drop('alliance_diplomacies');
	}
}
