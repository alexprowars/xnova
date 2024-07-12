<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Mailing extends Page
{
	protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
	protected static ?string $navigationGroup = 'Игра';
	protected static ?string $navigationLabel = 'Рассылка';
	protected static ?int $navigationSort = 120;
	protected static ?string $slug = 'mailing';

	protected static string $view = 'filament.pages.dashboard';
}
