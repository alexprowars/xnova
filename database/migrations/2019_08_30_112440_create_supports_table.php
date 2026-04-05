<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('support_tickets', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
			$table->string('subject')->nullable();
			$table->text('message');
			$table->smallInteger('status')->default(1);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('support_tickets');
	}
};
