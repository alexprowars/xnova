<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('user_teches', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id');
			$table->integer('tech_id')->nullable();
			$table->smallInteger('level')->default(0);
			$table->unique(['user_id','tech_id'], 'user_id');
		});
	}

	public function down()
	{
		Schema::drop('user_teches');
	}
};
