<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('queues', function (Blueprint $table) {
			$table->id();
			$table->enum('type', array('build','tech','unit'));
			$table->foreignId('user_id')->constrained('users');
			$table->integer('planet_id');
			$table->integer('object_id');
			$table->integer('level');
			$table->enum('operation', array('build','destroy'));
			$table->integer('time');
			$table->integer('time_end');
		});
	}

	public function down()
	{
		Schema::drop('queues');
	}
};
