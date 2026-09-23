<?php

namespace App\Filament\Resources\PlanetResource\Pages;

use App\Engine\Coordinates;
use App\Exceptions\Exception;
use App\Facades\Galaxy;
use App\Filament\Resources\PlanetResource;
use App\Models\Planet;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CreatePlanet extends CreateRecord
{
	protected static string $resource = PlanetResource::class;

	public function getTitle(): string
	{
		return __('admin.planets.create_planet');
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
					]),
			]);
	}

	protected function handleRecordCreation(array $data): Planet
	{
		$planet = Galaxy::createPlanet(
			new Coordinates($data['galaxy'], $data['system'], $data['planet']),
			User::findOne($data['user_id']),
			$data['name']
		);

		if (!$planet) {
			throw new Exception(__('admin.planets.planet_creation_failed'));
		}

		return $planet;
	}

	protected function getCreatedNotificationTitle(): ?string
	{
		return __('admin.planets.planet_created');
	}
}
