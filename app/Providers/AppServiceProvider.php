<?php

namespace App\Providers;

use App\Facades\Vars;
use App\Factories\PlanetServiceFactory;
use App\Services\GalaxyService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	public function boot()
	{
		JsonResource::withoutWrapping();

		Model::unguard();

		Date::use(CarbonImmutable::class);

		Builder::macro('findOne', /** @return Model|null */ function ($key) {
			if (empty($key)) {
				return null;
			}

			return $this->whereKey($key)->first();
		});

		Builder::macro('findOneOrFail', /** @return Model */ function ($key) {
			if (empty($key)) {
				return null;
			}

			return $this->whereKey($key)->firstOrFail();
		});

		Gate::before(function ($user) {
			return $user->hasRole('admin') ? true : null;
		});

		if (app()->isProduction()) {
			url()->forceHttps();

			DB::prohibitDestructiveCommands();
		}

		/*\DB::listen(function ($query) {
			dump($query);
		});*/
	}

	public function register()
	{
		if (str_starts_with(request()->path(), 'admin') || str_starts_with(request()->path(), 'livewire') || isset($_SERVER['LARAVEL_OCTANE'])) {
			$this->app->register(AdminPanelProvider::class);
		}

		$this->app->singleton(GalaxyService::class);
		$this->app->singleton(Vars::class);
		$this->app->singleton(PlanetServiceFactory::class);
	}
}
