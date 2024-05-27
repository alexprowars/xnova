<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('users_blocked', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->string('reason');
			$table->timestamp('longer')->nullable();
			$table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('users_blocked');
	}
};
