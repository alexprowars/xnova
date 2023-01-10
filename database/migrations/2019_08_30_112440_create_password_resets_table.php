<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('password_resets', function (Blueprint $table) {
			$table->string('email', 50)->index();
			$table->string('token', 50);
			$table->timestamp('created_at')->nullable();
		});
	}

	public function down()
	{
		Schema::drop('password_resets');
	}
};
