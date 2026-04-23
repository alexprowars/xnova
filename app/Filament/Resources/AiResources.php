<?php

namespace App\Filament\Resources;

use App\Models\Ai;
use Filament\Resources\Resource;

class AiResources extends Resource
{
	protected static ?string $model = Ai::class;

	protected static ?int $navigationSort = 121;
	protected static ?string $modelLabel = 'Ai';
	protected static ?string $pluralModelLabel = 'Ai';

	public static function getNavigationIcon(): string
	{
		return 'lucide-brain';
	}

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.game');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.ai');
	}

	public static function canAccess(): bool
	{
		return auth()->user()->can('ai');
	}

	public static function getPages(): array
	{
		return [
			'index' => AiResources\Pages\ListAi::route('/'),
			'create' => AiResources\Pages\CreateAi::route('/create'),
			'edit' => AiResources\Pages\EditAi::route('/{record}/edit'),
		];
	}
}
