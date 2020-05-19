<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlliancesTable extends Migration
{
	public function up()
	{
		Schema::create('alliances', function (Blueprint $table) {
			$table->id();
			$table->string('name', 32);
			$table->string('tag', 8);
			$table->integer('owner')->default(0);
			$table->integer('create_time')->default(0);
			$table->text('description')->nullable();
			$table->string('web')->nullable();
			$table->text('text')->nullable();
			$table->integer('image')->default(0);
			$table->text('request')->nullable();
			$table->boolean('request_notallow')->default(0);
			$table->string('owner_range', 32)->nullable();
			$table->text('ranks')->nullable();
			$table->boolean('members')->default(1);
		});
	}

	public function down()
	{
		Schema::drop('alliance');
	}
}
