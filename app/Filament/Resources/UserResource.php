<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Filament\Resources\UserResource\Pages;
use Filament\Resources\Resource;

class UserResource extends Resource
{
	protected static ?string $model = User::class;

	protected static ?int $navigationSort = 10;

	public static function getModelLabel(): string
	{
		return __('admin.common.user');
	}

	public static function getPluralModelLabel(): string
	{
		return __('admin.users.users');
	}

	protected static ?string $recordTitleAttribute = 'username';

	public static function getNavigationIcon(): string
	{
		return 'heroicon-o-users';
	}

	public static function getNavigationGroup(): string
	{
		return __('admin.groups.management');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.pages.users');
	}

	public static function canAccess(): bool
	{
		return auth()->user()->can('users');
	}

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListUsers::route('/'),
			'edit' => Pages\EditUser::route('/{record}/edit'),
			'create' => Pages\CreateUser::route('/create'),
			'view' => Pages\ViewUsers::route('/{record}'),
		];
	}
}
