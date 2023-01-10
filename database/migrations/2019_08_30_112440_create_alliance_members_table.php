<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('alliance_members', function (Blueprint $table) {
			$table->integer('a_id')->default(0)->index('a_id');
			$table->integer('u_id')->default(0)->unique('u_id');
			$table->boolean('rank')->default(0);
			$table->integer('time')->default(0);
		});
	}

	public function down()
	{
		Schema::drop('alliance_members');
	}
};
