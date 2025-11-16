<?php

namespace App\Filament\Resources\MoonResource\Pages;

use App\Engine\Coordinates;
use App\Exceptions\Exception;
use App\Facades\Galaxy;
use App\Filament\Resources\MoonResource;
use App\Models\Planet;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CreateMoon extends CreateRecord
{
	protected static string $resource = MoonResource::class;
	protected static ?string $title = 'Создать луну';

	public function form(Schema $schema): Schema
	{
		return $schema
			->columns(1)
			->schema([
				Section::make()
					->schema([
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
						TextInput::make('diameter')
							->label('Диаметр')
							->integer()
							->required()
							->default(1)
							->minValue(1)
							->maxValue(20),
					]),
			]);
	}

	protected function handleRecordCreation(array $data): Planet
	{
		$diameter = min(max($data['diameter'], 20), 0);

		$moon = Galaxy::createMoon(
			new Coordinates($data['galaxy'], $data['system'], $data['planet']),
			User::findOne($data['user_id']),
			$diameter
		);

		if (!$moon) {
			throw new Exception('Не удалось создать луну');
		}

		return $moon;
	}

	protected function getCreatedNotificationTitle(): ?string
	{
		return 'Луна создана';
	}
}
