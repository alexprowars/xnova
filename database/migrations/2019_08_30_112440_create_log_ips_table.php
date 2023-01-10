<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('log_ips', function (Blueprint $table) {
			$table->integer('id')->default(0);
			$table->integer('time')->default(0);
			$table->integer('ip')->unsigned()->default(0);
		});
	}

	public function down()
	{
		Schema::drop('log_ips');
	}
};
