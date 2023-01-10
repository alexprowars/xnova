<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('assault_users', function (Blueprint $table) {
			$table->id();
			$table->integer('aks_id')->unsigned()->default(0)->index('aks_id');
			$table->unsignedBigInteger('user_id');
		});
	}

	public function down()
	{
		Schema::drop('assault_users');
	}
};
