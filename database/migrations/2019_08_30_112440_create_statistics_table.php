<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('statistics', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
			$table->string('username', 35)->default('');
			$table->boolean('race')->default(0);
			$table->foreignId('alliance_id')->nullable()->constrained('users')->nullOnDelete();
			$table->string('alliance_name', 50)->default('');
			$table->boolean('stat_type')->default(0)->index();
			$table->integer('stat_code')->default(0);
			$table->smallInteger('tech_rank')->unsigned()->default(0)->index();
			$table->smallInteger('tech_old_rank')->unsigned()->default(0);
			$table->bigInteger('tech_points')->default(0);
			$table->integer('tech_count')->default(0);
			$table->smallInteger('build_rank')->unsigned()->default(0);
			$table->smallInteger('build_old_rank')->unsigned()->default(0);
			$table->bigInteger('build_points')->default(0);
			$table->integer('build_count')->default(0);
			$table->smallInteger('defs_rank')->unsigned()->default(0)->index();
			$table->smallInteger('defs_old_rank')->unsigned()->default(0);
			$table->bigInteger('defs_points')->default(0);
			$table->integer('defs_count')->default(0);
			$table->smallInteger('fleet_rank')->unsigned()->default(0)->index();
			$table->smallInteger('fleet_old_rank')->unsigned()->default(0);
			$table->bigInteger('fleet_points')->default(0);
			$table->integer('fleet_count')->default(0);
			$table->smallInteger('total_rank')->unsigned()->default(0)->index();
			$table->smallInteger('total_old_rank')->unsigned()->default(0);
			$table->bigInteger('total_points')->default(0);
			$table->integer('total_count')->default(0);
			$table->boolean('stat_hide')->default(0);
			$table->index(['stat_type','stat_code','stat_hide'], 'stat_type_2');
		});
	}

	public function down()
	{
		Schema::drop('statistics');
	}
};
