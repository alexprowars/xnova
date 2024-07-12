<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoonResource\Pages;
use App\Models\Planet;
use Filament\Resources\Resource;

class MoonResource extends Resource
{
	protected static ?string $model = Planet::class;

	protected static ?string $navigationIcon = 'heroicon-o-moon';
	protected static ?string $navigationGroup = 'Игра';
	protected static ?string $navigationLabel = 'Список лун';
	protected static ?int $navigationSort = 70;
	protected static ?string $modelLabel = 'Луна';
	protected static ?string $pluralModelLabel = 'Луны';
	protected static ?string $recordTitleAttribute = 'name';

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListMoons::route('/'),
			'create' => Pages\CreateMoon::route('/create'),
			'edit' => Pages\EditMoon::route('/{record}/edit'),
		];
	}
}
