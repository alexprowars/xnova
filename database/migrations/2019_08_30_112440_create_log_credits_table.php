<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('logs_credits', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->integer('amount')->default(0);
			$table->tinyInteger('type')->default(0);
			$table->timestamp('created_at')->useCurrent();
		});
	}

	public function down()
	{
		Schema::drop('logs_credits');
	}
};
