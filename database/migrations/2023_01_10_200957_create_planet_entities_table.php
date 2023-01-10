<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('planet_entities', function (Blueprint $table) {
			$table->id();
			$table->foreignId('planet_id')->constrained('planets')->cascadeOnUpdate()->restrictOnDelete();
			$table->integer('entity_id');
			$table->integer('amount')->default(0);
			$table->tinyInteger('factor')->default(10);
		});
	}

	public function down()
	{
		Schema::dropIfExists('planet_entities');
	}
};
