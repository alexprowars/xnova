<?php

namespace App\Filament\Resources;

use App\Models\Content;
use App\Filament\Resources\ContentResource\Pages;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class ContentResource extends Resource
{
	protected static ?string $model = Content::class;

	protected static ?string $navigationIcon = 'heroicon-o-document-text';
	protected static ?int $navigationSort = 110;
	protected static ?string $modelLabel = 'Контент';
	protected static ?string $pluralModelLabel = 'Контент';
	protected static ?string $recordTitleAttribute = 'title';

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.game');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.content');
	}

	public static function canAccess(): bool
	{
		return auth()->user()->can('content');
	}

	public static function form(Form $form): Form
	{
		return $form
			->schema([
				Section::make()
					->schema([
						TextInput::make('title')
							->label('Название'),
						TextInput::make('alias')
							->label('Символьный код'),
						RichEditor::make('html')
							->label('Контент'),
					]),
			]);
	}

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListContents::route('/'),
			'create' => Pages\CreateContent::route('/create'),
			'edit' => Pages\EditContent::route('/{record}/edit'),
		];
	}
}
