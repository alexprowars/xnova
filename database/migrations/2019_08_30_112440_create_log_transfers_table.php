<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('logs_transfers', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->noActionOnDelete();
			$table->json('data')->nullable();
			$table->foreignId('target_id')->constrained('users')->noActionOnDelete();
			$table->timestamp('created_at')->useCurrent();
		});
	}

	public function down()
	{
		Schema::drop('logs_transfers');
	}
};
