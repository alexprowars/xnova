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
			->emptyStateHeading(__('admin.messages.messages_not_found'))
			->defaultPaginationPageOption(25)
			->columns([
				TextColumn::make('id')
					->label('ID')
					->sortable(),
				TextColumn::make('date')
					->label(__('admin.common.date'))
					->dateTime()
					->sortable(),
				TextColumn::make('type')
					->label(__('admin.common.type'))
					->sortable(),
				TextColumn::make('from')
					->label(__('admin.messages.from'))
					->getStateUsing(fn(Message $record) => $record->from ? $record->from->username_formatted . ' ID:' . $record->from_id : 'SYSTEM')
					->sortable(),
				TextColumn::make('user')
					->label(__('admin.messages.to'))
					->getStateUsing(fn(Message $record) => $record->user ? $record->user->username_formatted . ' ID:' . $record->user_id : 'SYSTEM')
					->sortable(),
				TextColumn::make('subject')
					->label(__('admin.messages.subject'))
					->html()
					->getStateUsing(function (Message $record) {
						if ($message = MessageFactory::get($record->message)) {
							return $message->getSubject();
						}

						return null;
					}),
				TextColumn::make('message')
					->label(__('admin.messages.text'))
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
					->label(__('admin.messages.sender'))
					->relationship('user', 'username')
					->native(false)
					->searchable(['id', 'username', 'email']),
				SelectFilter::make('user_id')
					->label(__('admin.messages.to'))
					->relationship('user', 'username')
					->native(false)
					->searchable(['id', 'username', 'email']),
				DateFilter::make('time')
					->label(__('admin.common.date')),
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
