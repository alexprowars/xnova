<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('users_quests', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users');
			$table->integer('quest_id')->default(0);
			$table->boolean('finish')->default(false);
			$table->integer('stage')->default(0);
		});
	}

	public function down()
	{
		Schema::drop('users_quests');
	}
};
