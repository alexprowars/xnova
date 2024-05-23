<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('fleets_shortcuts', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users');
			$table->string('name', 100);
			$table->integer('galaxy');
			$table->integer('system');
			$table->integer('planet');
			$table->integer('planet_type')->default(1);
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('fleets_shortcuts');
	}
};
