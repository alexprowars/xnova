<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('files', function (Blueprint $table) {
			$table->id();
			$table->string('src', 200)->default('');
			$table->string('name', 100)->default('');
			$table->integer('size')->default(0);
			$table->string('mime', 100)->default('');
		});
	}

	public function down()
	{
		Schema::drop('files');
	}
};
