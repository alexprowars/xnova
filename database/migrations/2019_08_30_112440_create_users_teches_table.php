<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('users_teches', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users');
			$table->integer('tech_id')->nullable();
			$table->smallInteger('level')->default(0);
			$table->timestamps();
			$table->unique(['user_id','tech_id'], 'user_id');
		});
	}

	public function down()
	{
		Schema::drop('users_teches');
	}
};
