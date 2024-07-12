<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Filament\Resources\UserResource\Pages;
use Filament\Resources\Resource;

class UserResource extends Resource
{
	protected static ?string $model = User::class;

	protected static ?string $navigationIcon = 'heroicon-o-users';
	protected static ?string $navigationGroup = 'Администрирование';
	protected static ?string $navigationLabel = 'Пользователи';
	protected static ?int $navigationSort = 10;

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListUsers::route('/'),
			'edit' => Pages\EditUser::route('/{record}/edit'),
		];
	}
}
