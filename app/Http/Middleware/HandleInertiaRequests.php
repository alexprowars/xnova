<?php

namespace App\Http\Middleware;

use App\Http\Controllers\StateController;
use App\Settings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
	/**
	 * @return array<string, mixed>
	 */
	public function share(Request $request): array
	{
		$settings = app(Settings::class);

		$state = new StateController()->index($settings);

		//Inertia::once();

		return [
			...parent::share($request),
			...$state,
		];
	}
}
