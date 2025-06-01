<?php

namespace App\Providers;

use App\Filament\AvatarProviders\GravatarProvider;
use App\Filament\Resources\UserResource;
use Filament\Actions\CreateAction;
use Filament\Events\ServingFilament;
use Filament\FontProviders\LocalFontProvider;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Event;
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
			->userMenuItems([
				'profile' => MenuItem::make()
					->label(fn() => __('admin.edit_profile'))
					->url(fn() => UserResource::getUrl('edit', ['record' => auth()->user()], false)),
			])
			->topNavigation()
			->databaseTransactions()
			->globalSearch(false)
			->sidebarCollapsibleOnDesktop()
			->sidebarWidth('17rem')
			->maxContentWidth(MaxWidth::ScreenTwoExtraLarge)
			->readOnlyRelationManagersOnResourceViewPagesByDefault(false)
			->defaultAvatarProvider(GravatarProvider::class)
			->font('Helvetica Neue', provider: LocalFontProvider::class)
			->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
			->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
			->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
			->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
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
			])
			->plugin(FilamentSpatieLaravelBackupPlugin::make())
			->bootUsing(function (Panel $panel) {
				$this->afterBoot();

				Event::listen(function (ServingFilament $event) use ($panel) {
					$panel->navigationGroups([
						NavigationGroup::make('game')
							->label(__('admin.navigation.groups.game'))
							->icon('lucide-gamepad')
							->collapsed(),
						NavigationGroup::make('management')
							->label(__('admin.navigation.groups.management'))
							->icon('lucide-hammer')
							->collapsed(),
						NavigationGroup::make('settings')
							->label(__('admin.navigation.groups.settings'))
							->icon('lucide-cog')
							->collapsed(),
					]);
				});
			});
	}

	protected function afterBoot(): void
	{
		Tables\Table::configureUsing(function (Tables\Table $table): void {
			$table
				->persistFiltersInSession()
				->persistSortInSession()
				->paginationPageOptions([10, 20, 50, 100])
				->defaultPaginationPageOption(20)
				->extremePaginationLinks()
				->selectCurrentPageOnly()
				->columnToggleFormMaxHeight('500px')
				->striped()
				->deferFilters();
		});

		Tables\Table::$defaultDateDisplayFormat = 'd.m.Y';
		Tables\Table::$defaultDateTimeDisplayFormat = 'd.m.Y H:i:s';
		Infolist::$defaultDateTimeDisplayFormat = 'd.m.Y H:i:s';
		Infolist::$defaultDateDisplayFormat = 'd.m.Y';

		CreateAction::configureUsing(function (CreateAction $action): void {
			$action->createAnother(false);
		});

		Tables\Actions\CreateAction::configureUsing(function (Tables\Actions\CreateAction $action): void {
			$action->createAnother(false)
				->icon('lucide-circle-plus')
				->modalWidth(MaxWidth::ExtraLarge);
		});

		Tables\Actions\EditAction::configureUsing(function (Tables\Actions\EditAction $action): void {
			$action->modalWidth(MaxWidth::ExtraLarge);
		});

		Infolists\Components\Actions\Action::configureUsing(function (Infolists\Components\Actions\Action $action): void {
			$action->modalWidth(MaxWidth::ExtraLarge);
		});

		TextArea::configureUsing(function (TextArea $textarea): void {
			$textarea->rows(5);
		});

		DateTimePicker::configureUsing(function (DateTimePicker $datepicker): void {
			$datepicker->native(false)
				->displayFormat('d.m.Y H:i:s')
				->weekStartsOnMonday()
				->closeOnDateSelection();
		});

		DatePicker::configureUsing(function (DatePicker $datepicker): void {
			$datepicker->native(false)
				->displayFormat('d.m.Y')
				->weekStartsOnMonday()
				->closeOnDateSelection();
		});

		CreateRecord::disableCreateAnother();

		Resource::scopeToTenant(false);

		FilamentIcon::register([
			'panels::topbar.global-search.field' => 'lucide-search',
			'actions::view-action' => 'lucide-eye',
			'actions::edit-action' => 'lucide-edit',
			'actions::delete-action' => 'lucide-trash-2',
			'actions::make-collection-root-action' => 'lucide-corner-left-up',
			'tables::empty-state' => 'lucide-x',
		]);
	}
}
