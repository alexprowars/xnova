<?php

namespace App\Filament\Resources;

use App\Models\Message;
use App\Filament\Resources\MessageResource\Pages;
use Filament\Resources\Resource;

class MessageResource extends Resource
{
	protected static ?string $model = Message::class;

	protected static ?string $navigationIcon = 'heroicon-o-envelope';
	protected static ?int $navigationSort = 90;
	protected static ?string $modelLabel = 'Сообщение';
	protected static ?string $pluralModelLabel = 'Сообщения';
	protected static ?string $recordTitleAttribute = 'id';

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.game');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.messages');
	}

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListMessages::route('/'),
			'edit' => Pages\EditMessage::route('/{record}/edit'),
		];
	}
}
