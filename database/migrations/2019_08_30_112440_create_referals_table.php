<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('referals', function (Blueprint $table) {
			$table->id();
			$table->timestamp('date')->useCurrent();
			$table->foreignId('referal_id')->constrained('users')->cascadeOnDelete();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
		});
	}

	public function down()
	{
		Schema::drop('referals');
	}
};
