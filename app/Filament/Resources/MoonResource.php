<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoonResource\Pages;
use App\Models\Planet;
use Filament\Resources\Resource;

class MoonResource extends Resource
{
	protected static ?string $model = Planet::class;

	protected static ?string $navigationIcon = 'heroicon-o-moon';
	protected static ?int $navigationSort = 70;
	protected static ?string $modelLabel = 'Луна';
	protected static ?string $pluralModelLabel = 'Луны';
	protected static ?string $recordTitleAttribute = 'name';

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.game');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.moons');
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
