<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('alliances_chats', function (Blueprint $table) {
			$table->id();
			$table->timestamp('date')->useCurrent();
			$table->foreignId('alliance_id')->constrained('alliances')->cascadeOnDelete();
			$table->string('user', 50)->default('');
			$table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
			$table->text('message');
		});
	}

	public function down()
	{
		Schema::drop('alliances_chats');
	}
};
