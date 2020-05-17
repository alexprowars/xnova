<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFriendsTable extends Migration
{
	public function up()
	{
		Schema::create('friends', function (Blueprint $table) {
			$table->bigInteger('id', true);
			$table->integer('sender')->default(0)->index('sender');
			$table->integer('owner')->default(0)->index('owner');
			$table->boolean('ignor')->default(0);
			$table->boolean('active')->default(0);
			$table->string('text', 250);
		});
	}

	public function down()
	{
		Schema::drop('buddy');
	}
}
