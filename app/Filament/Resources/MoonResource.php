<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoonResource\Pages;
use App\Models\Planet;
use Filament\Resources\Resource;

class MoonResource extends Resource
{
	protected static ?string $model = Planet::class;

	protected static ?int $navigationSort = 70;

	public static function getModelLabel(): string
	{
		return __('admin.moons.moon');
	}

	public static function getPluralModelLabel(): string
	{
		return __('admin.moons.moons');
	}

	protected static ?string $recordTitleAttribute = 'name';

	public static function getNavigationIcon(): string
	{
		return 'heroicon-o-moon';
	}

	public static function getNavigationGroup(): string
	{
		return __('admin.groups.game');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.pages.moons');
	}

	public static function canAccess(): bool
	{
		return auth()->user()->can('moons');
	}

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListMoons::route('/'),
			'create' => Pages\CreateMoon::route('/create'),
			'edit' => Pages\EditMoon::route('/{record}/edit'),
		];
	}
}
