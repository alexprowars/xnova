<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('alliances_diplomacies', function (Blueprint $table) {
			$table->id();
			$table->foreignId('alliance_id')->constrained('alliances')->cascadeOnDelete();
			$table->foreignId('diplomacy_id')->constrained('alliances')->cascadeOnDelete();
			$table->boolean('type')->default(0);
			$table->boolean('status')->default(0);
			$table->boolean('primary')->default(0);
			$table->index(['alliance_id','diplomacy_id']);
		});
	}

	public function down()
	{
		Schema::drop('alliances_diplomacies');
	}
};
