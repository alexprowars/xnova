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
						TextInput::make('diameter')
							->label(__('admin.common.diameter'))
							->integer()
							->required(),
					]),
			]);
	}
}
