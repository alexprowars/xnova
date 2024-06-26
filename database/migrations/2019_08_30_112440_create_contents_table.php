<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('contents', function (Blueprint $table) {
			$table->id();
			$table->string('title', 150)->default('');
			$table->string('alias', 100)->default('')->unique('alias');
			$table->text('html');
		});
	}

	public function down()
	{
		Schema::drop('contents');
	}
};
