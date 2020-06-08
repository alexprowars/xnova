<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserQuestsTable extends Migration
{
	public function up()
	{
		Schema::create('user_quests', function (Blueprint $table) {
			$table->id();
			$table->integer('user_id')->default(0)->index('user_id');
			$table->integer('quest_id')->default(0);
			$table->enum('finish', array('0','1'))->default('0');
			$table->integer('stage')->default(0);
		});
	}

	public function down()
	{
		Schema::drop('user_quests');
	}
}