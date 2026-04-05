<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('logs_battles', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->nullable()->constrained('users');
			$table->string('title')->default('');
			$table->json('data');
			$table->timestamp('created_at')->useCurrent();
		});
	}

	public function down()
	{
		Schema::drop('logs_battles');
	}
};
