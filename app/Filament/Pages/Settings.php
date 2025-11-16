<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Settings extends SettingsPage
{
	protected static string $settings = \App\Settings::class;

	public static function getNavigationIcon(): string
	{
		return 'heroicon-o-cog-6-tooth';
	}

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.settings');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.settings');
	}

	public function getTitle(): string
	{
		return __('admin.navigation.pages.settings');
	}

	public static function canAccess(): bool
	{
		return auth()->user()->can('settings');
	}

	public function form(Schema $schema): Schema
	{
		return $schema
			->components([
				Section::make([
					Textarea::make('globalMessage')
						->label('Глобальное сообщение'),
					TextInput::make('lastSettedGalaxyPos')
						->label('Галактика')
						->integer()
						->required(),
					TextInput::make('lastSettedSystemPos')
						->label('Система')
						->integer()
						->required(),
					TextInput::make('lastSettedPlanetPos')
						->label('Планета')
						->integer()
						->required(),
				])
				->heading('Последняя позиция при регистрации')

			])
			->columns(1);
	}
}
