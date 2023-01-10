<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('notes', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id')->index('owner');
			$table->integer('time')->nullable();
			$table->boolean('priority')->nullable();
			$table->string('title', 60)->nullable();
			$table->text('text')->nullable();
		});
	}

	public function down()
	{
		Schema::drop('notes');
	}
};
