<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Server extends Page
{
	protected static ?string $navigationIcon = 'heroicon-o-server-stack';
	protected static ?string $navigationGroup = 'Игра';
	protected static ?string $navigationLabel = 'Информация';
	protected static ?string $title = 'Переменные сервера';
	protected static ?int $navigationSort = 30;
	protected static ?string $slug = 'server';

	protected static string $view = 'filament.pages.server';
}
