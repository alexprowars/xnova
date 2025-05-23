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

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.game');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.planets');
	}

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListPlanets::route('/'),
			'view' => Pages\ViewPlanet::route('/{record}'),
			'create' => Pages\CreatePlanet::route('/create'),
			'edit' => Pages\EditPlanet::route('/{record}/edit'),
		];
	}
}
