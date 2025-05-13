<?php

namespace App\Filament\Resources\PlanetResource\Pages;

use App\Engine\Coordinates;
use App\Exceptions\Exception;
use App\Facades\Galaxy;
use App\Filament\Resources\PlanetResource;
use App\Models\Planet;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreatePlanet extends CreateRecord
{
	protected static string $resource = PlanetResource::class;
	protected static ?string $title = 'Создать планету';

	public function form(Form $form): Form
	{
		return $form
			->columns(1)
			->schema([
				TextInput::make('name')
					->label('Название')
					->maxLength(50)
					->default(__('main.sys_colo_defaultname')),
				Select::make('user_id')
					->label('Игрок')
					->relationship('user', 'username'),
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
			]);
	}

	protected function handleRecordCreation(array $data): Planet
	{
		$planetId = Galaxy::createPlanet(
			new Coordinates($data['galaxy'], $data['system'], $data['planet']),
			$data['user_id'],
			$data['name']
		);

		if (!$planetId) {
			throw new Exception('Не удалось создать планету');
		}

		return Planet::find($planetId);
	}

	protected function getCreatedNotificationTitle(): ?string
	{
		return 'Планета создана';
	}
}
