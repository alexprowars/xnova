<?php

namespace App\Admin;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Access
{
	public const array RESOURCES = [
		'users',
		'roles',
		'planets',
		'moons',
		'fleets',
		'alliances',
		'messages',
		'ai',
		'content',
		'settings',
	];

	public static function allows(User $user, string $permission): bool
	{
		return $user->id === 1 || $user->can($permission);
	}

	public static function authorize(string $permission): void
	{
		$user = auth()->user();

		abort_unless($user && self::allows($user, 'panel') && self::allows($user, $permission), 403);
	}

	public static function isSuperuser(User $user): bool
	{
		return $user->id === 1 || $user->hasRole('admin');
	}

	public static function canManageUser(User $actor, User $user): bool
	{
		if (self::isSuperuser($actor)) {
			return true;
		}

		return !self::isSuperuser($user)
			&& $user->getAllPermissions()->pluck('id')
				->diff($actor->getAllPermissions()->pluck('id'))
				->isEmpty();
	}

	public static function authorizeUser(User $user): void
	{
		$actor = auth()->user();

		abort_unless($actor && self::canManageUser($actor, $user), 403);
	}

	public static function canManageRole(User $actor, Role $role): bool
	{
		if (self::isSuperuser($actor)) {
			return true;
		}

		return strtolower(trim($role->name ?? '')) !== 'admin'
			&& $role->permissions->every(fn(Permission $permission) => self::canGrantPermission($actor, $permission));
	}

	public static function authorizeRole(Role $role): void
	{
		$actor = auth()->user();

		abort_unless($actor && self::canManageRole($actor, $role), 403);
	}

	public static function canGrantPermission(User $actor, Permission $permission): bool
	{
		return self::isSuperuser($actor) || $actor->getAllPermissions()->contains('id', $permission->id);
	}

	public static function shared(User $user): array
	{
		$permissions = array_values(
			array_filter(
				[...self::RESOURCES, 'users-block', 'users-unblock', 'mailing'],
				fn(string $permission) => self::allows($user, $permission),
			),
		);

		return [
			'username' => $user->username,
			'permissions' => $permissions,
			'navigation' => array_map(
				fn(string $resource) => [
					'key' => $resource,
					'label' => __('admin.' . $resource),
					'url' => '/admin/' . $resource,
				],
				array_values(array_intersect(self::RESOURCES, $permissions)),
			),
			'labels' => __('admin'),
			'flash' => session('admin_notice'),
		];
	}
}
