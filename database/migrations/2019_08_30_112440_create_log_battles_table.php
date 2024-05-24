<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('log_battles', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users');
			$table->string('title')->default('');
			$table->json('data');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('log_battles');
	}
};
