<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Engine\Messages\MessageFactory;
use App\Filament\Components\Table\Filters\DateFilter;
use App\Filament\Resources\MessageResource;
use App\Models\Message;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Throwable;

class ListMessages extends ListRecords
{
	protected static string $resource = MessageResource::class;

	public function table(Table $table): Table
	{
		return $table
			->defaultSort('id', 'desc')
			->emptyStateHeading('Сообщения не найдены')
			->defaultPaginationPageOption(25)
			->columns([
				TextColumn::make('id')
					->label('ID')
					->sortable(),
				TextColumn::make('date')
					->label('Дата')
					->dateTime()
					->sortable(),
				TextColumn::make('type')
					->label('Тип')
					->sortable(),
				TextColumn::make('from')
					->label('От')
					->getStateUsing(fn(Message $record) => $record->from ? $record->from->username_formatted . ' ID:' . $record->from_id : 'SYSTEM')
					->sortable(),
				TextColumn::make('user')
					->label('Кому')
					->getStateUsing(fn(Message $record) => $record->user ? $record->user->username_formatted . ' ID:' . $record->user_id : 'SYSTEM')
					->sortable(),
				TextColumn::make('subject')
					->label('Тема')
					->html()
					->getStateUsing(function (Message $record) {
						if ($message = MessageFactory::get($record->message)) {
							return $message->getSubject();
						}

						return null;
					}),
				TextColumn::make('message')
					->label('Текст')
					->html()
					->sortable()
					->searchable()
					->getStateUsing(function (Message $record) {
						if ($message = MessageFactory::get($record->message)) {
							try {
								return $message->render();
							} catch (Throwable $e) {
								return 'render message error: ' . $e->getMessage();
							}
						}

						return null;
					}),
			])
			->filters([
				SelectFilter::make('from_id')
					->label('От кого')
					->relationship('user', 'username')
					->native(false)
					->searchable(['id', 'username', 'email']),
				SelectFilter::make('user_id')
					->label('Кому')
					->relationship('user', 'username')
					->native(false)
					->searchable(['id', 'username', 'email']),
				DateFilter::make('time')
					->label('Дата'),
			])
			->recordActions([
				EditAction::make()
					->iconButton(),
				DeleteAction::make()
					->iconButton(),
			])
			->toolbarActions([
				BulkActionGroup::make([
					DeleteBulkAction::make(),
				]),
			]);
	}
}
