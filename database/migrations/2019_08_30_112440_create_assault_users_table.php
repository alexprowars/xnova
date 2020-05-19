<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssaultUsersTable extends Migration
{
	public function up()
	{
		Schema::create('assault_users', function (Blueprint $table) {
			$table->id();
			$table->integer('aks_id')->unsigned()->default(0)->index('aks_id');
			$table->integer('user_id')->unsigned()->default(0);
		});
	}

	public function down()
	{
		Schema::drop('assault_users');
	}
}
