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
		return __('admin.groups.settings');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.pages.settings');
	}

	public function getTitle(): string
	{
		return __('admin.pages.settings');
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
						->label(__('admin.settings.global_message')),
					TextInput::make('lastSettedGalaxyPos')
						->label(__('admin.common.galaxy'))
						->integer()
						->required(),
					TextInput::make('lastSettedSystemPos')
						->label(__('admin.common.system'))
						->integer()
						->required(),
					TextInput::make('lastSettedPlanetPos')
						->label(__('admin.common.planet'))
						->integer()
						->required(),
				])
				->heading(__('admin.settings.registration_position'))

			])
			->columns(1);
	}
}
