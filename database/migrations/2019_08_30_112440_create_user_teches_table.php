<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTechsTable extends Migration
{
	public function up()
	{
		Schema::create('user_teches', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('user_id')->nullable();
			$table->integer('tech_id')->nullable();
			$table->smallInteger('level')->default(0);
			$table->unique(['user_id','tech_id'], 'user_id');
		});
	}

	public function down()
	{
		Schema::drop('user_teches');
	}
}
