<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Filament\Resources\MessageResource;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditMessage extends EditRecord
{
	protected static string $resource = MessageResource::class;
	protected static ?string $title = 'Редактирование сообщения';

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
				Select::make('from_id')
					->label('От кого')
					->relationship('from', 'username')
					->searchable()
					->nullable()
					->default(null),
				Select::make('user_id')
					->label('Кому')
					->relationship('user', 'username')
					->searchable()
					->required(),
				DateTimePicker::make('time')
					->label('Дата')
					->required(),
				Select::make('type')
					->label('Тип')
					->options(MessageType::class)
					->required(),
				TextInput::make('theme')
					->label('Тема'),
				RichEditor::make('text')
					->label('Текст')
					->required(),
			]);
	}
}
