<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Support extends Page
{
	protected static ?string $navigationIcon = 'heroicon-o-lifebuoy';
	protected static ?string $navigationGroup = 'Игра';
	protected static ?string $navigationLabel = 'Техподдержка';
	protected static ?int $navigationSort = 20;
	protected static ?string $slug = 'support';

	protected static string $view = 'filament.pages.dashboard';
}
