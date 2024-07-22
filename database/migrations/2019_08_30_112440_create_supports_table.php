<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('supports', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
			$table->string('subject')->default('');
			$table->text('message');
			$table->integer('status')->default(1);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('supports');
	}
};
