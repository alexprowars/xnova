<?php

namespace App\Filament\Resources;

use App\Models\Fleet;
use App\Filament\Resources\FleetResource\Pages;
use Filament\Resources\Resource;

class FleetResource extends Resource
{
	protected static ?string $model = Fleet::class;

	protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
	protected static ?int $navigationSort = 80;
	protected static ?string $modelLabel = 'Флот';
	protected static ?string $pluralModelLabel = 'Флот';
	protected static ?string $recordTitleAttribute = 'name';

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.game');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.fleets');
	}

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListFleets::route('/'),
		];
	}
}
