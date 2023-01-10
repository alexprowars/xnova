<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('moneys', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id');
			$table->string('ip', 50)->default('')->index('ip');
			$table->bigInteger('time')->default(0)->index('time');
			$table->string('referer', 250)->default('');
			$table->string('user_agent', 250)->default('');
		});
	}

	public function down()
	{
		Schema::drop('moneys');
	}
};
