<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('contents', function (Blueprint $table) {
			$table->renameColumn('title', 'title_ru');
			$table->renameColumn('html', 'html_ru');
		});

		Schema::table('contents', function (Blueprint $table) {
			$table->string('title_en', 150)->nullable();
			$table->text('html_en')->nullable();
		});
	}

	public function down(): void
	{
		Schema::table('contents', function (Blueprint $table) {
			$table->dropColumn(['title_en', 'html_en']);
		});

		Schema::table('contents', function (Blueprint $table) {
			$table->renameColumn('title_ru', 'title');
			$table->renameColumn('html_ru', 'html');
		});
	}
};
