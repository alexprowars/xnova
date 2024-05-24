<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('log_ips', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->noActionOnDelete();
			$table->integer('ip')->unsigned()->default(0);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('log_ips');
	}
};
