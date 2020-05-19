<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
	public function up()
	{
		Schema::create('reports', function (Blueprint $table) {
			$table->id();
			$table->string('id_users')->default('');
			$table->text('raport');
			$table->boolean('no_contact')->default(0);
			$table->integer('time')->unsigned()->default(0);
		});
	}

	public function down()
	{
		Schema::drop('reports');
	}
}
