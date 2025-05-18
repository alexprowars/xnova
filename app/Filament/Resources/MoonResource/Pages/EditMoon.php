<?php

namespace App\Filament\Resources\MoonResource\Pages;

use App\Filament\Resources\MoonResource;
use Filament\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditMoon extends EditRecord
{
	protected static string $resource = MoonResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Actions\DeleteAction::make(),
		];
	}

	public function form(Form $form): Form
	{
		return $form
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
						TextInput::make('diameter')
							->label('Диаметр')
							->integer()
							->required(),
					]),
			]);
	}
}
