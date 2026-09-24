<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('ai', function (Blueprint $table) {
			$table->timestamp('next_run_at')->nullable();
			$table->index(['active', 'next_run_at']);
		});
	}

	public function down(): void
	{
		Schema::table('ai', function (Blueprint $table) {
			$table->dropIndex(['active', 'next_run_at']);
			$table->dropColumn('next_run_at');
		});
	}
};
