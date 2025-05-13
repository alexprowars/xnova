<?php

namespace App\Providers;

use App\Engine\Galaxy;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
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

		/*\DB::listen(function ($query) {
			dump($query);
		});*/
	}

	public function register()
	{
		if (str_starts_with(request()->path(), 'admin') || str_starts_with(request()->path(), 'livewire') || isset($_SERVER['LARAVEL_OCTANE'])) {
			$this->app->register(AdminPanelProvider::class);
		}

		$this->app->singleton(Galaxy::class);
	}
}
