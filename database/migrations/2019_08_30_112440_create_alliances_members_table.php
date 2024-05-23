<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('alliances_members', function (Blueprint $table) {
			$table->id();
			$table->foreignId('alliance_id')->constrained('alliances')->cascadeOnDelete();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->boolean('rank')->default(0);
			$table->integer('time')->default(0);
		});
	}

	public function down()
	{
		Schema::drop('alliances_members');
	}
};
