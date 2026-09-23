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

	public function getTitle(): string
	{
		return __('admin.moons.create_moon');
	}

	public function form(Schema $schema): Schema
	{
		return $schema
			->columns(1)
			->schema([
				Section::make()
					->schema([
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
						TextInput::make('diameter')
							->label(__('admin.common.diameter'))
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
		$diameter = min(max($data['diameter'], 1), 20);

		$moon = Galaxy::createMoon(
			new Coordinates($data['galaxy'], $data['system'], $data['planet']),
			User::findOne($data['user_id']),
			$diameter
		);

		if (!$moon) {
			throw new Exception(__('admin.moons.moon_creation_failed'));
		}

		return $moon;
	}

	protected function getCreatedNotificationTitle(): ?string
	{
		return __('admin.moons.moon_created');
	}
}
