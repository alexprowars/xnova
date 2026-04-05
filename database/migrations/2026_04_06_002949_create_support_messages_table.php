<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('support_messages', function (Blueprint $table) {
			$table->id();
			$table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
			$table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
			$table->string('message');
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('support_messages');
	}
};
