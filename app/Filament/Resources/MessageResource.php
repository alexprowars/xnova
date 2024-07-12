<?php

namespace App\Filament\Resources;

use App\Models\Message;
use App\Filament\Resources\MessageResource\Pages;
use Filament\Resources\Resource;

class MessageResource extends Resource
{
	protected static ?string $model = Message::class;

	protected static ?string $navigationIcon = 'heroicon-o-envelope';
	protected static ?string $navigationGroup = 'Игра';
	protected static ?string $navigationLabel = 'Сообщения';
	protected static ?int $navigationSort = 90;
	protected static ?string $modelLabel = 'Сообщение';
	protected static ?string $pluralModelLabel = 'Сообщения';
	protected static ?string $recordTitleAttribute = 'id';

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListMessages::route('/'),
			'edit' => Pages\EditMessage::route('/{record}/edit'),
		];
	}
}
