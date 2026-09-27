<?php

namespace App\Admin;

use App\Models\User;

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
