<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllianceRequestsTable extends Migration
{
	public function up()
	{
		Schema::create('alliance_requests', function (Blueprint $table) {
			$table->integer('a_id')->default(0);
			$table->integer('u_id')->default(0);
			$table->integer('time')->default(0);
			$table->string('request');
			$table->unique(['a_id','u_id'], 'a_id');
		});
	}

	public function down()
	{
		Schema::drop('alliance_requests');
	}
}
