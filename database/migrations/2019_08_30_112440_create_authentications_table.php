<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthenticationsTable extends Migration
{
	public function up()
	{
		Schema::create('authentications', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('user_id')->default(0)->index('user_id');
			$table->string('provider', 50);
			$table->string('provider_id')->index('external_id');
			$table->integer('create_time')->default(0);
			$table->integer('enter_time')->default(0);
		});
	}

	public function down()
	{
		Schema::drop('authentications');
	}
}
