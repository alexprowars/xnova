<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Alliances extends Page
{
	protected static ?string $navigationIcon = 'heroicon-o-user-group';
	protected static ?string $navigationGroup = 'Игра';
	protected static ?string $navigationLabel = 'Список альянсов';
	protected static ?int $navigationSort = 100;
	protected static ?string $slug = 'alliances';

	protected static string $view = 'filament.pages.dashboard';
}
