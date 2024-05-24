<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('users_authentications', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->string('provider', 50);
			$table->string('provider_id')->index();
			$table->timestamp('enter_time')->nullable();
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('users_authentications');
	}
};
