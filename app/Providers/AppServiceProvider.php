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
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
	public function boot(): void
	{
		Model::unguard();

		Date::use(CarbonImmutable::class);

		JsonResource::withoutWrapping();

		Str::macro('sanitize', function (string $string): string {
			return htmlspecialchars($string);
		});

		Builder::macro('findOne', function ($key) {
			if (empty($key)) {
				return null;
			}

			/** @var Builder<Model> $this */
			return $this->whereKey($key)->first();
		});

		Builder::macro('findOneOrFail', function ($key) {
			if (empty($key)) {
				return null;
			}

			/** @var Builder<Model> $this */
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
		if (!str_starts_with(request()->path(), 'api') || isset($_SERVER['LARAVEL_OCTANE'])) {
			$this->app->register(AdminPanelProvider::class);
		}

		$this->app->singleton(GalaxyService::class);
		$this->app->singleton(Vars::class);
		$this->app->singleton(PlanetServiceFactory::class);
	}
}
