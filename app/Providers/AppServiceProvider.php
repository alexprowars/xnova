<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	public function boot()
	{
		JsonResource::withoutWrapping();

		/*\DB::listen(function ($query) {
			dump($query);
		});*/
	}

	public function register()
	{
		if (str_starts_with(request()->path(), 'admin') || str_starts_with(request()->path(), 'livewire') || isset($_SERVER['LARAVEL_OCTANE'])) {
			$this->app->register(AdminPanelProvider::class);
		}
	}
}
