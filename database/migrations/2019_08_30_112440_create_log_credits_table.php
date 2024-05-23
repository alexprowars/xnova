<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('log_credits', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users');
			$table->integer('amount')->default(0);
			$table->boolean('type')->default(0);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('log_credits');
	}
};
