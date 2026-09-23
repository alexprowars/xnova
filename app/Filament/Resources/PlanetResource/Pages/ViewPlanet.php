<?php

namespace App\Filament\Resources\PlanetResource\Pages;

use App\Filament\Resources\PlanetResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class ViewPlanet extends ViewRecord
{
	protected static string $resource = PlanetResource::class;

	public function infolist(Schema $schema): Schema
	{
		return $schema
			->schema([
				TextEntry::make('id')
					->label('ID'),
				TextEntry::make('name')
					->label(__('admin.common.title')),
				TextEntry::make('user_id')
					->label(__('admin.common.player')),
				Fieldset::make(__('admin.common.coordinates'))
					->columns(4)
					->schema([
						TextEntry::make('galaxy')
							->label(__('admin.common.galaxy')),
						TextEntry::make('system')
							->label(__('admin.common.system')),
						TextEntry::make('planet')
							->label(__('admin.common.planet')),
						TextEntry::make('planet_type')
							->label(__('admin.common.type')),
					]),
				TextEntry::make('last_update')
					->dateTime()
					->label(__('admin.planets.updated_at')),
				TextEntry::make('last_active')
					->dateTime()
					->label(__('admin.planets.activity_time')),
				TextEntry::make('destroyed_at')
					->dateTime()
					->label(__('admin.planets.destroyed_at')),
				TextEntry::make('merchand')
					->dateTime()
					->label(__('admin.planets.resources_purchased_at')),
				TextEntry::make('image')
					->label(__('admin.planets.image')),
				TextEntry::make('diameter')
					->label(__('admin.common.diameter')),
				TextEntry::make('field_current')
					->label(__('admin.planets.field_count')),
				TextEntry::make('field_max')
					->label(__('admin.planets.max_field_count')),
				TextEntry::make('temp_min')
					->label(__('admin.planets.min_temperature')),
				TextEntry::make('temp_max')
					->label(__('admin.planets.max_temperature')),
				Fieldset::make(__('admin.planets.resources'))
					->columns(3)
					->schema([
						TextEntry::make('metal')
							->label(__('admin.planets.metal'))
							->numeric(4, ',', ' '),
						TextEntry::make('crystal')
							->label(__('admin.planets.crystal'))
							->numeric(4, ',', ' '),
						TextEntry::make('deuterium')
							->label(__('admin.planets.deuterium'))
							->numeric(4, ',', ' '),
					]),
				Fieldset::make(__('admin.planets.debris_field'))
					->schema([
						TextEntry::make('debris_metal')
							->label(__('admin.planets.metal')),
						TextEntry::make('debris_crystal')
							->label(__('admin.planets.crystal')),
					]),
			])
			->columns(1);
	}
}
