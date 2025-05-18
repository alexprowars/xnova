<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class Settings extends SettingsPage
{
	protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
	protected static string $settings = \App\Settings::class;

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

	public function form(Form $form): Form
	{
		return $form
			->schema([
				Forms\Components\Section::make([
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
