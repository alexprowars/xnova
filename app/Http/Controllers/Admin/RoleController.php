<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Access;
use App\Admin\Presentation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends AdminController
{
	public function index(Request $request)
	{
		Access::authorize('roles');

		$columns = ['id', 'name', 'guard_name', 'permissions_count'];

		return $this->listing(
			$request,
			'roles',
			Role::query()->withCount('permissions'),
			$columns,
			[Presentation::field('name')],
			fn(Role $role) => Presentation::record($role, $columns),
		);
	}

	private function fields(?Role $role = null): array
	{
		return Presentation::fill(
			[
				Presentation::field('name'),
				Presentation::field(
					'guard_name',
					'select',
					Presentation::options(['web' => 'web', 'api' => 'api']),
					'web',
				),
				Presentation::field(
					'permissions',
					'checkboxes',
					Permission::query()
						->orderBy('name')
						->get()
						->map(
							fn(Permission $permission) => [
								'value' => $permission->id,
								'label' => $permission->name . ' (' . $permission->guard_name . ')',
							],
						)
						->all(),
					[],
				),
			],
			$role,
		);
	}

	public function create()
	{
		Access::authorize('roles');

		return $this->form('roles', $this->fields());
	}

	public function edit(Role $role)
	{
		Access::authorize('roles');

		return $this->form('roles', $this->fields($role), $role->id);
	}

	public function store(Request $request)
	{
		return $this->update($request, new Role());
	}

	public function update(Request $request, Role $role)
	{
		Access::authorize('roles');

		$data = $request->validate([
			'name' => [
				'required',
				'string',
				'max:255',
				Rule::unique('roles')->where('guard_name', $request->input('guard_name'))->ignore($role->id),
			],
			'guard_name' => ['required', Rule::in(['web', 'api'])],
			'permissions' => ['present', 'array'],
			'permissions.*' => [
				'integer',
				'distinct',
				Rule::exists('permissions', 'id')->where('guard_name', $request->input('guard_name')),
			],
		]);

		if ($role->exists && $role->guard_name !== $data['guard_name']) {
			throw ValidationException::withMessages([
				'guard_name' => __('admin.guard_immutable'),
			]);
		}

		DB::transaction(function () use ($role, $data) {
			$permissions = Permission::query()
				->whereIn('id', $data['permissions'])
				->get();

			unset($data['permissions']);

			$role->fill($data)->save();
			$role->syncPermissions($permissions);
		});

		return redirect('/admin/roles/' . $role->id . '/edit')->with('admin_notice', __('admin.saved'));
	}
}
