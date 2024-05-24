<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('planets_entities', function (Blueprint $table) {
			$table->id();
			$table->foreignId('planet_id')->constrained('planets')->cascadeOnUpdate()->cascadeOnDelete();
			$table->integer('entity_id');
			$table->integer('amount')->default(0);
			$table->tinyInteger('factor')->default(10);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('planets_entities');
	}
};
