<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlliancesResource\RelationManagers\MembersRelation;
use App\Models\Alliance;
use Filament\Resources\Resource;

class AlliancesResource extends Resource
{
	protected static ?string $model = Alliance::class;

	protected static ?string $navigationIcon = 'heroicon-o-user-group';
	protected static ?int $navigationSort = 100;
	protected static ?string $modelLabel = 'Альянс';
	protected static ?string $pluralModelLabel = 'Альянсы';
	protected static ?string $recordTitleAttribute = 'name';

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.game');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.alliances');
	}

	public static function canAccess(): bool
	{
		return auth()->user()->can('alliances');
	}

	public static function getRelations(): array
	{
		return [
			MembersRelation::class,
		];
	}

	public static function getPages(): array
	{
		return [
			'index' => AlliancesResource\Pages\ListAlliance::route('/'),
			//'create' => AlliancesResource\Pages\CreateContent::route('/create'),
			'edit' => AlliancesResource\Pages\EditAlliance::route('/{record}/edit'),
		];
	}
}
