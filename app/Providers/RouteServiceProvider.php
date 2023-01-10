<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers;

class RouteServiceProvider extends Providers\RouteServiceProvider
{
	public const HOME = '/';
	protected $namespace = 'App\Http\Controllers';

	public function boot()
	{
	}

	public function map()
	{
		$this->mapApiRoutes();
		$this->mapAdminRoutes();
	}

	protected function mapApiRoutes()
	{
		Route::prefix('api')
			->middleware('api')
			->namespace($this->namespace)
			->group(base_path('routes/api.php'));
	}

	protected function mapAdminRoutes()
	{
		Route::prefix('admin')
			->middleware('admin')
			->namespace($this->namespace . '\Admin')
			->group(base_path('routes/admin.php'));
	}
}
