<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('users', function (Blueprint $table) {
			$table->id();
			$table->string('email', 50);
			$table->string('password', 100)->default('');
			$table->string('username', 50);
			$table->boolean('authlevel')->default(0);
			$table->smallInteger('group_id')->default(0);
			$table->integer('banned')->default(0);
			$table->integer('onlinetime')->unsigned()->default(0);
			$table->bigInteger('ip')->default(0);
			$table->boolean('sex')->default(0);
			$table->boolean('race')->default(0);
			$table->integer('planet_id')->unsigned()->default(0);
			$table->integer('planet_current')->unsigned()->default(0);
			$table->integer('bonus')->unsigned()->default(0);
			$table->foreignId('alliance_id')->nullable()->constrained('alliances')->nullOnDelete();
			$table->string('alliance_name', 50)->nullable();
			$table->smallInteger('lvl_minier')->unsigned()->default(1);
			$table->smallInteger('lvl_raid')->unsigned()->default(1);
			$table->integer('xpminier')->unsigned()->default(0);
			$table->integer('xpraid')->unsigned()->default(0);
			$table->smallInteger('credits')->unsigned()->default(0);
			$table->smallInteger('messages')->unsigned()->default(0);
			$table->smallInteger('messages_ally')->unsigned()->default(0);
			$table->smallInteger('avatar')->unsigned()->default(0);
			$table->integer('galaxy')->unsigned()->default(0);
			$table->integer('system')->unsigned()->default(0);
			$table->boolean('planet')->default(0);
			$table->integer('vacation')->unsigned()->default(0);
			$table->integer('deltime')->default(0);
			$table->integer('rpg_geologue')->unsigned()->default(0);
			$table->integer('rpg_admiral')->unsigned()->default(0);
			$table->integer('rpg_ingenieur')->unsigned()->default(0);
			$table->integer('rpg_technocrate')->unsigned()->default(0);
			$table->integer('rpg_constructeur')->unsigned()->default(0);
			$table->integer('rpg_meta')->unsigned()->default(0);
			$table->integer('rpg_komandir')->default(0);
			$table->smallInteger('raids_win')->unsigned()->default(0);
			$table->smallInteger('raids_lose')->unsigned()->default(0);
			$table->integer('raids')->default(0);
			$table->boolean('bonus_multi')->default(0);
			$table->integer('refers')->default(0);
			$table->integer('message_block')->default(0);
			$table->integer('links')->unsigned()->default(0);
			$table->boolean('chat')->default(0);
			$table->json('options')->nullable();
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('users');
	}
};
