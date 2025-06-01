<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('friends', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->foreignId('friend_id')->constrained('users')->cascadeOnDelete();
			$table->boolean('ignore')->default(false);
			$table->boolean('active')->default(false);
			$table->string('message', 250);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('friends');
	}
};
