<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('moneys', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users');
			$table->string('ip', 50)->default('')->index();
			$table->timestamp('time')->nullable()->index();
			$table->string('referer', 250)->default('');
			$table->string('user_agent', 250)->default('');
		});
	}

	public function down()
	{
		Schema::drop('moneys');
	}
};
