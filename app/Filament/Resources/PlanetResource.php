<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanetResource\Pages;
use App\Models\Planet;
use Filament\Resources\Resource;

class PlanetResource extends Resource
{
	protected static ?string $model = Planet::class;

	protected static ?int $navigationSort = 60;

	public static function getModelLabel(): string
	{
		return __('admin.common.planet');
	}

	public static function getPluralModelLabel(): string
	{
		return __('admin.planets.planets');
	}

	protected static ?string $recordTitleAttribute = 'name';
	protected static ?string $slug = 'planets';

	public static function getNavigationIcon(): string
	{
		return 'heroicon-o-globe-alt';
	}

	public static function getNavigationGroup(): string
	{
		return __('admin.groups.game');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.pages.planets');
	}

	public static function canAccess(): bool
	{
		return auth()->user()->can('planets');
	}

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListPlanets::route('/'),
			'create' => Pages\CreatePlanet::route('/create'),
			'view' => Pages\ViewPlanet::route('/{record}'),
			'edit' => Pages\EditPlanet::route('/{record}/edit'),
		];
	}
}
