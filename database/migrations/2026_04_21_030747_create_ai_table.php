<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('ai', function (Blueprint $table) {
			$table->id();
			$table->boolean('active')->default(false);
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->string('strategy');
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('ai');
	}
};
