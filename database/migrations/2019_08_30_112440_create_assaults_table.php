<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssaultsTable extends Migration
{
	public function up()
	{
		Schema::create('assaults', function (Blueprint $table) {
			$table->id();
			$table->string('name', 50)->nullable();
			$table->integer('fleet_id')->nullable();
			$table->integer('galaxy')->nullable();
			$table->integer('system')->nullable();
			$table->integer('planet')->nullable();
			$table->boolean('planet_type')->default(1);
			$table->integer('user_id')->nullable()->default(0);
		});
	}

	public function down()
	{
		Schema::drop('assaults');
	}
}
