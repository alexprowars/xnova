<?php

namespace App\Filament\Resources;

use App\Models\Content;
use App\Filament\Resources\ContentResource\Pages;
use Filament\Resources\Resource;

class ContentResource extends Resource
{
	protected static ?string $model = Content::class;

	protected static ?string $navigationIcon = 'heroicon-o-document-text';
	protected static ?string $navigationGroup = 'Игра';
	protected static ?string $navigationLabel = 'Контент';
	protected static ?int $navigationSort = 110;
	protected static ?string $modelLabel = 'Контент';
	protected static ?string $pluralModelLabel = 'Контент';
	protected static ?string $recordTitleAttribute = 'title';

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListContents::route('/'),
			'create' => Pages\CreateContent::route('/create'),
			'edit' => Pages\EditContent::route('/{record}/edit'),
		];
	}
}
