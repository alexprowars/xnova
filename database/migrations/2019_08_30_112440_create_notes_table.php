<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotesTable extends Migration
{
	public function up()
	{
		Schema::create('notes', function (Blueprint $table) {
			$table->bigInteger('id', true);
			$table->integer('user_id')->nullable()->index('owner');
			$table->integer('time')->nullable();
			$table->boolean('priority')->nullable();
			$table->string('title', 60)->nullable();
			$table->text('text')->nullable();
		});
	}

	public function down()
	{
		Schema::drop('notes');
	}
}
