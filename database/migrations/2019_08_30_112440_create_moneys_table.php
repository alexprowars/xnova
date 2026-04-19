<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('referrals_clicks', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->bigInteger('ip')->nullable()->index();
			$table->timestamp('date')->useCurrent()->index();
			$table->string('referer', 255)->nullable();
			$table->string('user_agent', 255)->nullable();
		});
	}

	public function down()
	{
		Schema::drop('referrals_clicks');
	}
};
