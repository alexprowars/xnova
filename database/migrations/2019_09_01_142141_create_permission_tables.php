<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
	public function up()
	{
		$tableNames = config('permission.table_names');
		$columnNames = config('permission.column_names');

		Schema::create($tableNames['permissions'], function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('guard_name');
			$table->timestamps();
		});

		Schema::create($tableNames['roles'], function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('guard_name');
			$table->timestamps();
		});

		Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames) {
			$table->unsignedBigInteger('permission_id');
			$table->string('model_type');
			$table->unsignedBigInteger($columnNames['model_morph_key']);
			$table->index([$columnNames['model_morph_key'], 'model_type', ]);

			$table->foreign('permission_id')
				->references('id')
				->on($tableNames['permissions'])
				->cascadeOnDelete();

			$table->primary(
				['permission_id', $columnNames['model_morph_key'], 'model_type'],
				'model_has_permissions_permission_model_type_primary'
			);
		});

		Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames) {
			$table->unsignedBigInteger('role_id');
			$table->string('model_type');
			$table->unsignedBigInteger($columnNames['model_morph_key']);
			$table->index([$columnNames['model_morph_key'], 'model_type', ]);

			$table->foreign('role_id')
				->references('id')
				->on($tableNames['roles'])
				->cascadeOnDelete();

			$table->primary(
				['role_id', $columnNames['model_morph_key'], 'model_type'],
				'model_has_roles_role_model_type_primary'
			);
		});

		Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
			$table->unsignedBigInteger('permission_id');
			$table->unsignedBigInteger('role_id');

			$table->foreign('permission_id')
				->references('id')
				->on($tableNames['permissions'])
				->cascadeOnDelete();

			$table->foreign('role_id')
				->references('id')
				->on($tableNames['roles'])
				->cascadeOnDelete();

			$table->primary(['permission_id', 'role_id']);
		});

		app('cache')
			->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
			->forget(config('permission.cache.key'));
	}

	public function down()
	{
		$tableNames = config('permission.table_names');

		Schema::drop($tableNames['role_has_permissions']);
		Schema::drop($tableNames['model_has_roles']);
		Schema::drop($tableNames['model_has_permissions']);
		Schema::drop($tableNames['roles']);
		Schema::drop($tableNames['permissions']);
	}
};
