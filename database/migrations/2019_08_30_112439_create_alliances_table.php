<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('alliances', function (Blueprint $table) {
			$table->id();
			$table->string('name', 32);
			$table->string('tag', 8);
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->text('description')->nullable();
			$table->string('web')->nullable();
			$table->text('text')->nullable();
			$table->integer('image')->default(0);
			$table->text('request')->nullable();
			$table->boolean('request_notallow')->default(0);
			$table->string('owner_range', 32)->nullable();
			$table->json('ranks')->nullable();
			$table->integer('members_count')->default(0);
			$table->timestamps();
		});

		Schema::table('users', function (Blueprint $table) {
			$table->foreign('alliance_id')->on('alliances')->references('id')->nullOnDelete();
		});
	}

	public function down()
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropForeign('alliance_id');
		});

		Schema::drop('alliances');
	}
};
