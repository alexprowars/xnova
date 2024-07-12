<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Manager extends Page
{
	protected static ?string $navigationIcon = 'heroicon-o-pencil';
	protected static ?string $navigationGroup = 'Игра';
	protected static ?string $navigationLabel = 'Редактор';
	protected static ?int $navigationSort = 50;
	protected static ?string $slug = 'manager';

	protected static string $view = 'filament.pages.dashboard';
}
