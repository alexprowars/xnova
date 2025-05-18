<?php

namespace App\Filament\Resources\AlliancesResource\Pages;

use App\Filament\Resources\AlliancesResource;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditAlliance extends EditRecord
{
	protected static string $resource = AlliancesResource::class;
	protected static ?string $title = 'Редактирование альянса';

	public function form(Form $form): Form
	{
		return $form
			->schema([
				Section::make()
					->schema([
						TextInput::make('name')
							->label('Имя')
							->required(),
						TextInput::make('tag')
							->label('Тэг')
							->required(),
						Select::make('user_id')
							->label('Лидер')
							->relationship('user', 'username')
							->native(false)
							->searchable(['id', 'username', 'email']),
						TextInput::make('web')
							->label('Сайт'),
						RichEditor::make('description')
							->label('Описание'),
						SpatieMediaLibraryFileUpload::make('photo')
							->label('Логотип'),
					]),
			]);
	}
}
