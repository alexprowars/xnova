<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('reports', function (Blueprint $table) {
			$table->id();
			$table->json('users_id');
			$table->json('data');
			$table->boolean('no_contact')->default(false);
			$table->timestamps();
		});

		Schema::table('halls_of_fame', function (Blueprint $table) {
			$table->foreignId('report_id')->nullable()->constrained('reports')->nullOnDelete();
		});
	}

	public function down()
	{
		Schema::drop('reports');
	}
};
