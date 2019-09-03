<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersPaymentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users_payments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('user')->default(0);
			$table->string('product_code', 100)->default('');
			$table->integer('call_id')->default(0);
			$table->string('method', 100)->default('');
			$table->bigInteger('transaction_id')->default(0);
			$table->dateTime('transaction_time');
			$table->bigInteger('uid')->default(0);
			$table->integer('amount')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users_payments');
	}

}
