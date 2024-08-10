<?php

namespace App\Providers;

use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Http\Middleware\AdminCanAccess;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;

class AdminPanelProvider extends PanelProvider
{
	public function panel(Panel $panel): Panel
	{
		return $panel
			->default()
			->id('admin')
			->path('admin')
			->colors([
				'primary' => Color::Amber,
			])
			->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
			->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
			->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
			->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
			->navigationGroups([
				'Игра',
				'Администрирование',
			])
			->middleware([
				EncryptCookies::class,
				AddQueuedCookiesToResponse::class,
				StartSession::class,
				AuthenticateSession::class,
				ShareErrorsFromSession::class,
				VerifyCsrfToken::class,
				SubstituteBindings::class,
				DisableBladeIconComponents::class,
				DispatchServingFilamentEvent::class,
			])
			->authMiddleware([
				Authenticate::class,
				AdminCanAccess::class,
			])
			->plugin(FilamentSpatieLaravelBackupPlugin::make())
			->plugin(FilamentSpatieRolesPermissionsPlugin::make());
	}
}
