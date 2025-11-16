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
	protected static ?string $title = 'Создать планету';

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
			throw new Exception('Не удалось создать планету');
		}

		return $planet;
	}

	protected function getCreatedNotificationTitle(): ?string
	{
		return 'Планета создана';
	}
}
