<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('settings', function (Blueprint $table) {
			$table->id();
			$table->string('key')->unique();
			$table->string('name');
			$table->string('description')->nullable();
			$table->text('value')->nullable();
			$table->text('field');
			$table->tinyInteger('active');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('settings');
	}
};
