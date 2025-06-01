<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Server extends Page
{
	protected static ?string $navigationIcon = 'heroicon-o-server-stack';
	protected static ?int $navigationSort = 40;
	protected static ?string $slug = 'server';

	protected static string $view = 'filament.pages.server';

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.management');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.server');
	}

	public function getTitle(): string
	{
		return __('admin.navigation.pages.server');
	}

	public static function canAccess(): bool
	{
		return auth()->user()->can('server');
	}
}
