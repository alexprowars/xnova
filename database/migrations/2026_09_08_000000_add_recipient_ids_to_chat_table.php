<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::table('chat', function (Blueprint $table) {
			$table->json('recipient_ids')->nullable();
		});
	}

	public function down()
	{
		Schema::table('chat', function (Blueprint $table) {
			$table->dropColumn('recipient_ids');
		});
	}
};
