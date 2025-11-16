<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Engine\Enums\MessageType;
use App\Filament\Resources\MessageResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditMessage extends EditRecord
{
	protected static string $resource = MessageResource::class;
	protected static ?string $title = 'Редактирование сообщения';

	protected function getHeaderActions(): array
	{
		return [
			DeleteAction::make(),
		];
	}

	public function form(Schema $schema): Schema
	{
		return $schema
			->components([
				Section::make()
					->schema([
						Select::make('from_id')
							->label('От кого')
							->relationship('from', 'username')
							->native(false)
							->searchable(['id', 'username', 'email'])
							->nullable()
							->default(null),
						Select::make('user_id')
							->label('Кому')
							->relationship('user', 'username')
							->native(false)
							->searchable(['id', 'username', 'email'])
							->required(),
						DateTimePicker::make('time')
							->label('Дата')
							->required(),
						Select::make('type')
							->label('Тип')
							->options(MessageType::class)
							->required(),
						TextInput::make('subject')
							->label('Тема'),
						RichEditor::make('message')
							->label('Текст')
							->required(),
					]),
			]);
	}
}
