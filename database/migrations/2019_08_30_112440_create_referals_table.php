<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('referals', function (Blueprint $table) {
			$table->integer('r_id')->unsigned()->default(0)->primary();
			$table->integer('u_id')->unsigned()->default(0);
		});
	}

	public function down()
	{
		Schema::drop('referals');
	}
};
