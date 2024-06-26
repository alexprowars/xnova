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
			$table->tinyInteger('rank')->nullable();
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('alliances_members');
	}
};
