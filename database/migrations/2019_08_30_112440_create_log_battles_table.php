<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('logs_battles', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->nullable()->constrained('users');
			$table->string('title')->default('');
			$table->json('data');
			$table->timestamp('created_at')->useCurrent();
		});

		Schema::table('halls_of_fame', function (Blueprint $table) {
			$table->foreignId('report_id')->nullable()->constrained('logs_battles')->nullOnDelete();
		});
	}

	public function down()
	{
		Schema::table('halls_of_fame', function (Blueprint $table) {
			$table->dropConstrainedForeignId('report_id');
		});

		Schema::drop('logs_battles');
	}
};
