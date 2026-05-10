<?php

namespace App\Providers;

use App\Exceptions\PageException;
use App\Facades\Vars;
use App\Factories\PlanetServiceFactory;
use App\Http\PageResponseFactory;
use App\Services\GalaxyService;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\ExceptionResponse;
use Inertia\Inertia;
use Inertia\ResponseFactory;

class AppServiceProvider extends ServiceProvider
{
	public function boot(): void
	{
		Model::unguard();

		Date::use(CarbonImmutable::class);

		JsonResource::withoutWrapping();

		RateLimiter::for('global', fn () => Limit::perMinute(60)->by(request()->ip()));

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

		Inertia::disableSsr(function () {
			$path = request()->path();

			if ($path == '/' || str_starts_with($path, 'content') || str_starts_with($path, 'stats') || str_starts_with($path, 'players')) {
				return false;
			}

			return true;
		});

		Inertia::handleExceptionsUsing(function (ExceptionResponse $response) {
			if ($response->response instanceof JsonResponse) {
				return $response;
			}

			if ($response->exception instanceof PageException) {
				return $response->render(
					'Errors/Page',
					array_merge([
						'errors' => ['error' => $response->exception->getMessage()],
						'status' => $response->statusCode(),
						'message' => $response->exception->getMessage(),
					], !app()->isProduction() ? ['trace' => $response->exception->getTraceAsString()] : [])
				)->withSharedData();
			}

			if (in_array($response->statusCode(), [403, 404, 500, 503])) {
				return $response->render(
					'Errors/FullPage',
					array_merge([
						'errors' => ['error' => $response->exception->getMessage()],
						'status' => $response->statusCode(),
						'message' => $response->exception->getMessage(),
					], !app()->isProduction() ? ['trace' => $response->exception->getTraceAsString()] : [])
				);
			}

			return $response;
		});
	}

	public function register()
	{
		if (!str_starts_with(request()->path(), 'api') || isset($_SERVER['LARAVEL_OCTANE'])) {
			$this->app->register(AdminPanelProvider::class);
		}

		$this->app->singleton(GalaxyService::class);
		$this->app->singleton(Vars::class);
		$this->app->singleton(PlanetServiceFactory::class);
		$this->app->singleton(ResponseFactory::class, PageResponseFactory::class);
	}
}
