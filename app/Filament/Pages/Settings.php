<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class Settings extends SettingsPage
{
	protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
	protected static ?string $navigationGroup = 'Настройки';
	protected static ?string $navigationLabel = 'Настройки';
	protected static ?string $title = 'Настройки';

	protected static string $settings = \App\Settings::class;

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
