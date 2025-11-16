<?php

namespace App\Filament\Resources\MoonResource\Pages;

use App\Filament\Resources\MoonResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditMoon extends EditRecord
{
	protected static string $resource = MoonResource::class;

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
						TextInput::make('diameter')
							->label('Диаметр')
							->integer()
							->required(),
					]),
			]);
	}
}
