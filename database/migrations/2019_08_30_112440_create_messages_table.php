<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('messages', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->foreignId('from_id')->nullable()->constrained('users')->cascadeOnDelete();
			$table->timestamp('time')->nullable()->index();
			$table->integer('type')->default(0);
			$table->boolean('deleted')->default(false);
			$table->string('theme', 100)->nullable();
			$table->text('message')->nullable();
			$table->index(['user_id','deleted']);
		});
	}

	public function down()
	{
		Schema::drop('messages');
	}
};
