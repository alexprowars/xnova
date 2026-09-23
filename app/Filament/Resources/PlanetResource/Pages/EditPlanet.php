<?php

namespace App\Filament\Resources\PlanetResource\Pages;

use App\Engine\Enums\PlanetType;
use App\Filament\Resources\PlanetResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditPlanet extends EditRecord
{
	protected static string $resource = PlanetResource::class;

	protected function getHeaderActions(): array
	{
		return [
			DeleteAction::make(),
		];
	}

	public function form(Schema $schema): Schema
	{
		return $schema
			->columns(1)
			->schema([
				Section::make()
					->schema([
						TextInput::make('name')
							->label(__('admin.common.title'))
							->maxLength(50)
							->default(__('main.sys_colo_defaultname')),
						Select::make('user_id')
							->label(__('admin.common.player'))
							->relationship('user', 'username')
							->native(false)
							->searchable(['id', 'username', 'email']),
						TextInput::make('galaxy')
							->label(__('admin.common.galaxy'))
							->integer()
							->required(),
						TextInput::make('system')
							->label(__('admin.common.system'))
							->required()
							->integer(),
						TextInput::make('planet')
							->label(__('admin.common.planet'))
							->integer()
							->required(),
						Select::make('planet_type')
							->label(__('admin.common.type'))
							->options(PlanetType::class)
							->required(),
						TextInput::make('metal')
							->label(__('admin.planets.metal'))
							->required()
							->integer()
							->default(config('game.baseMetalProduction')),
						TextInput::make('crystal')
							->label(__('admin.planets.crystal'))
							->required()
							->integer()
							->default(config('game.baseCrystalProduction')),
						TextInput::make('deuterium')
							->label(__('admin.planets.deuterium'))
							->required()
							->integer()
							->default(config('game.baseDeuteriumProduction')),
						TextInput::make('debris_metal')
							->label(__('admin.planets.debris_metal'))
							->required()
							->integer()
							->default(0),
						TextInput::make('debris_crystal')
							->label(__('admin.planets.debris_crystal'))
							->required()
							->integer()
							->default(0),
					]),
			]);
	}
}
