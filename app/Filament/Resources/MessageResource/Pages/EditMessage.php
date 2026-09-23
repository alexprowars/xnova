<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Engine\Enums\MessageType;
use App\Filament\Resources\MessageResource;
use App\Models\Message;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditMessage extends EditRecord
{
	protected static string $resource = MessageResource::class;

	public function getTitle(): string
	{
		return __('admin.messages.edit_message');
	}

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
							->label(__('admin.messages.sender'))
							->relationship('from', 'username')
							->native(false)
							->searchable(['id', 'username', 'email'])
							->nullable()
							->default(null),
						Select::make('user_id')
							->label(__('admin.messages.to'))
							->relationship('user', 'username')
							->native(false)
							->searchable(['id', 'username', 'email'])
							->required(),
						DateTimePicker::make('time')
							->label(__('admin.common.date'))
							->required(),
						Select::make('type')
							->label(__('admin.common.type'))
							->options(MessageType::class)
							->required(),
						TextInput::make('subject')
							->label(__('admin.messages.subject')),
						TextEntry::make('message')
							->label(__('admin.messages.text'))
							->getStateUsing(fn(Message $record) => json_encode($record->message)),
					]),
			]);
	}
}
