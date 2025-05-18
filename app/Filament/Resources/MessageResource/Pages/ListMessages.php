<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Filament\Resources\MessageResource;
use App\Models\Message;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

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
				TextColumn::make('time')
					->label('Время')
					->dateTime()
					->sortable(),
				TextColumn::make('type')
					->label('Тип')
					->sortable(),
				TextColumn::make('from_id')
					->label('От')
					->formatStateUsing(fn(Message $record) => $record->from ? $record->from->username . ' ID:' . $record->from_id : '-')
					->sortable(),
				TextColumn::make('user_id')
					->label('Кому')
					->formatStateUsing(fn(Message $record) => $record->user ? $record->user->username . ' ID:' . $record->user_id : '-')
					->sortable(),
				TextColumn::make('text')
					->label('Текст')
					->html()
					->sortable()
					->searchable(),

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
				DateRangeFilter::make('time')
					->label('Дата'),
			])
			->actions([
				Tables\Actions\EditAction::make()
					->iconButton(),
			])
			->bulkActions([
				Tables\Actions\BulkActionGroup::make([
					Tables\Actions\DeleteBulkAction::make(),
				]),
			]);
	}
}
