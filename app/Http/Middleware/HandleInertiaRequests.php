<?php

namespace App\Http\Middleware;

use App\Admin\Access;
use App\Http\Controllers\StateController;
use App\Settings;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
	public function rootView(Request $request): string
	{
		return $request->is('admin', 'admin/*') ? 'admin' : 'app';
	}

	/**
	 * @return array<string, mixed>
	 */
	public function share(Request $request): array
	{
		if ($request->is('admin', 'admin/*')) {
			return [
				...parent::share($request),
				'admin' => function () use ($request) {
					$user = $request->user();

					return $user && Access::allows($user, 'panel') ? Access::shared($user) : null;
				},
			];
		}

		$settings = app(Settings::class);

		$state = new StateController()->index($settings);

		return [
			...parent::share($request),
			'state' => $state,
		];
	}
}
