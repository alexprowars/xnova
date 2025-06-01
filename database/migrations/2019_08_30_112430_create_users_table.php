<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('users', function (Blueprint $table) {
			$table->id();
			$table->string('email', 50)->unique();
			$table->timestamp('email_verified_at')->nullable();
			$table->string('password', 100)->nullable();
			$table->string('username', 50)->nullable();
			$table->timestamp('username_change')->nullable();
			$table->timestamp('blocked_at')->nullable();
			$table->timestamp('onlinetime')->nullable();
			$table->bigInteger('ip')->nullable();
			$table->tinyInteger('sex')->default(0);
			$table->tinyInteger('race')->default(0);
			$table->tinyInteger('race_change_count')->default(1);
			$table->unsignedBigInteger('planet_id')->nullable();
			$table->unsignedBigInteger('planet_current')->nullable();
			$table->unsignedBigInteger('alliance_id')->nullable();
			$table->string('alliance_name', 50)->nullable();
			$table->smallInteger('lvl_minier')->unsigned()->default(1);
			$table->smallInteger('lvl_raid')->unsigned()->default(1);
			$table->integer('xpminier')->unsigned()->default(0);
			$table->integer('xpraid')->unsigned()->default(0);
			$table->integer('credits')->unsigned()->default(0);
			$table->smallInteger('messages')->unsigned()->default(0);
			$table->smallInteger('messages_ally')->unsigned()->default(0);
			$table->smallInteger('avatar')->unsigned()->default(0);
			$table->integer('galaxy')->unsigned()->default(0);
			$table->integer('system')->unsigned()->default(0);
			$table->tinyInteger('planet')->default(0);
			$table->timestamp('vacation')->nullable();
			$table->timestamp('delete_time')->nullable();
			$table->timestamp('rpg_geologue')->nullable();
			$table->timestamp('rpg_admiral')->nullable();
			$table->timestamp('rpg_ingenieur')->nullable();
			$table->timestamp('rpg_technocrate')->nullable();
			$table->timestamp('rpg_constructeur')->nullable();
			$table->timestamp('rpg_meta')->nullable();
			$table->timestamp('rpg_komandir')->nullable();
			$table->smallInteger('raids_win')->unsigned()->default(0);
			$table->smallInteger('raids_lose')->unsigned()->default(0);
			$table->integer('raids')->default(0);
			$table->timestamp('daily_bonus')->nullable();
			$table->tinyInteger('daily_bonus_factor')->default(0);
			$table->integer('refers')->default(0);
			$table->timestamp('message_block')->nullable();
			$table->integer('links')->unsigned()->default(0);
			$table->tinyInteger('chat')->default(0);
			$table->text('about')->nullable();
			$table->json('options')->nullable();
			$table->char('locale', 2)->default('en');
			$table->rememberToken();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::drop('users');
	}
};
