<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Support extends Page
{
	protected static ?string $navigationIcon = 'heroicon-o-lifebuoy';
	protected static ?int $navigationSort = 20;
	protected static ?string $slug = 'support';

	protected static string $view = 'filament.pages.dashboard';

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.game');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.support');
	}
}
