<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanetResource\Pages;
use App\Models\Planet;
use Filament\Resources\Resource;

class PlanetResource extends Resource
{
	protected static ?string $model = Planet::class;

	protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
	protected static ?int $navigationSort = 60;
	protected static ?string $modelLabel = 'Планета';
	protected static ?string $pluralModelLabel = 'Планеты';
	protected static ?string $recordTitleAttribute = 'name';
	protected static ?string $slug = 'planets';

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.game');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.planets');
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
