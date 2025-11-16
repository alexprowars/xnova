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
							->label('Название')
							->maxLength(50)
							->default(__('main.sys_colo_defaultname')),
						Select::make('user_id')
							->label('Игрок')
							->relationship('user', 'username')
							->native(false)
							->searchable(['id', 'username', 'email']),
						TextInput::make('galaxy')
							->label('Галактика')
							->integer()
							->required(),
						TextInput::make('system')
							->label('Система')
							->required()
							->integer(),
						TextInput::make('planet')
							->label('Планета')
							->integer()
							->required(),
						Select::make('planet_type')
							->label('Тип')
							->options(PlanetType::class)
							->required(),
						TextInput::make('metal')
							->label('Металл')
							->required()
							->integer()
							->default(config('game.baseMetalProduction')),
						TextInput::make('crystal')
							->label('Кристалл')
							->required()
							->integer()
							->default(config('game.baseCrystalProduction')),
						TextInput::make('deuterium')
							->label('Дейтерий')
							->required()
							->integer()
							->default(config('game.baseDeuteriumProduction')),
						TextInput::make('debris_metal')
							->label('Поле обломков: Металл')
							->required()
							->integer()
							->default(0),
						TextInput::make('debris_crystal')
							->label('Поле обломков: Кристалл')
							->required()
							->integer()
							->default(0),
					]),
			]);
	}
}
