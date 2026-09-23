<?php

namespace App\Filament\Resources;

use App\Models\Message;
use App\Filament\Resources\MessageResource\Pages;
use Filament\Resources\Resource;

class MessageResource extends Resource
{
	protected static ?string $model = Message::class;

	protected static ?int $navigationSort = 90;

	public static function getModelLabel(): string
	{
		return __('admin.common.message');
	}

	public static function getPluralModelLabel(): string
	{
		return __('admin.messages.messages');
	}

	protected static ?string $recordTitleAttribute = 'id';

	public static function getNavigationIcon(): string
	{
		return 'heroicon-o-envelope';
	}

	public static function getNavigationGroup(): string
	{
		return __('admin.groups.game');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.pages.messages');
	}

	public static function canAccess(): bool
	{
		return auth()->user()->can('messages');
	}

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListMessages::route('/'),
			'edit' => Pages\EditMessage::route('/{record}/edit'),
		];
	}
}
