<?php

namespace App\Filament\Resources\AiResources\Pages;

use App\Engine\Ai\StrategyType;
use App\Filament\Resources\AiResources;
use App\Services\UserService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Nubs\RandomNameGenerator;

class CreateAi extends CreateRecord
{
	protected static string $resource = AiResources::class;
	protected static ?string $title = 'Создать бота';

	public function form(Schema $schema): Schema
	{
		return $schema
			->components([
				Section::make()
					->schema([
						Toggle::make('active')
							->label('Активность'),
						Select::make('strategy')
							->label('Стратегия')
							->required()
							->options(StrategyType::class),
					]),
			]);
	}

	protected function mutateFormDataBeforeCreate(array $data): array
	{
		$user = UserService::creation([
			'name' 		=> new RandomNameGenerator\Alliteration()->getName(),
			'email'    	=> Str::random(10) . '@local',
			'password' 	=> Str::random(),
		]);

		$user->race = random_int(1, 4);
		$user->sex = random_int(1, 2);
		$user->avatar = random_int(1, 8);
		$user->save();

		$data['user_id'] = $user->id;

		return $data;
	}
}
