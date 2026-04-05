<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('halls_of_fame', function (Blueprint $table) {
			$table->id();
			$table->string('title', 150);
			$table->integer('debris');
			$table->timestamp('date')->index();
			$table->boolean('won');
			$table->enum('type', ['single', 'team'])->default('single')->index();
			$table->foreignId('report_id')->nullable()->constrained('reports')->nullOnDelete();
		});
	}

	public function down()
	{
		Schema::drop('halls_of_fame');
	}
};
