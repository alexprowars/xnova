<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('assaults_users', function (Blueprint $table) {
			$table->id();
			$table->foreignId('assault_id')->constrained('assaults')->cascadeOnDelete();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
		});
	}

	public function down()
	{
		Schema::drop('assaults_users');
	}
};
