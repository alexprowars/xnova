<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OnlineList;
use App\Filament\Widgets\VersionWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
	public static function getNavigationIcon(): string
	{
		return 'lucide-house';
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.dashboard');
	}

	public function getTitle(): string
	{
		return __('admin.navigation.pages.dashboard');
	}

	public function getWidgets(): array
	{
		return [
			VersionWidget::class,
			OnlineList::class,
		];
	}
}
