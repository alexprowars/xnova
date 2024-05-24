<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('planets', function (Blueprint $table) {
			$table->id();
			$table->string('name', 50)->nullable();
			$table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
			$table->foreignId('alliance_id')->nullable()->constrained('alliances')->nullOnDelete();
			$table->boolean('id_level')->default(0);
			$table->boolean('galaxy')->default(0)->index();
			$table->smallInteger('system')->unsigned()->default(0)->index();
			$table->boolean('planet')->default(0);
			$table->integer('last_update')->nullable();
			$table->integer('last_active')->default(0);
			$table->boolean('planet_type')->default(1);
			$table->integer('destruyed')->unsigned()->default(0);
			$table->string('image', 32)->default('normaltempplanet01');
			$table->smallInteger('diameter')->unsigned()->default(12800);
			$table->smallInteger('field_current')->unsigned()->default(0);
			$table->smallInteger('field_max')->unsigned()->default(163);
			$table->smallInteger('temp_min')->default(-17);
			$table->smallInteger('temp_max')->default(23);
			$table->float('metal', 32, 4)->default(500.0000);
			$table->float('crystal', 32, 4)->default(500.0000);
			$table->float('deuterium', 32, 4)->default(0.0000);
			$table->integer('last_jump_time')->unsigned()->default(0);
			$table->foreignId('parent_planet')->nullable()->constrained('planets')->nullOnDelete();
			$table->integer('debris_metal')->default(0);
			$table->integer('debris_crystal')->default(0);
			$table->integer('merchand')->default(0);
		});

		Schema::table('users', function (Blueprint $table) {
			$table->foreign('planet_id')->on('planets')->references('id')->nullOnDelete();
			$table->foreign('planet_current')->on('planets')->references('id')->nullOnDelete();
		});
	}

	public function down()
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropForeign('planet_id');
			$table->dropForeign('planet_current');
		});

		Schema::drop('planets');
	}
};
