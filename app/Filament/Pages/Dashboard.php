<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OnlineList;
use App\Filament\Widgets\VersionWidget;
use Filament\Pages\Page;

class Dashboard extends Page
{
	protected static ?string $navigationIcon = 'heroicon-o-home';
	protected static ?int $navigationSort = 10;
	protected static ?string $slug = 'dashboard';

	protected static string $view = 'filament.pages.dashboard';

	public static function getNavigationLabel(): string
	{
		return 'Панель управления';
	}

	public function getTitle(): string
	{
		return 'Панель управления';
	}

	public function getWidgets(): array
	{
		return [
			VersionWidget::class,
			OnlineList::class,
		];
	}

	public function getColumns(): int|string|array
	{
		return 2;
	}

	public function getVisibleWidgets(): array
	{
		return $this->filterVisibleWidgets($this->getWidgets());
	}
}
