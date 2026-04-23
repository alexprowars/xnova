<?php

namespace App\Filament\Resources\AiResources\Pages;

use App\Filament\Resources\AiResources;
use App\Models\Ai;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListAi extends ListRecords
{
	protected static string $resource = AiResources::class;

	protected function getHeaderActions(): array
	{
		return [
			CreateAction::make(),
		];
	}

	public function table(Table $table): Table
	{
		return $table
			->defaultSort('id', 'desc')
			->defaultPaginationPageOption(25)
			->columns([
				TextColumn::make('id')
					->label('ID')
					->sortable(),
				TextColumn::make('strategy')
					->label('Стратегия'),
				TextColumn::make('user.id')
					->label('ID игрока'),
				TextColumn::make('user.username')
					->label('Имя'),
				TextColumn::make('user.galaxy')
					->label('Координаты')
					->formatStateUsing(function (Ai $record) {
						return $record->user->galaxy . ':' . $record->user->system . ':' . $record->user->planet;
					}),
				TextColumn::make('created_at')
					->label('Дата создания')
					->dateTime()
					->sortable(),
			])
			->recordActions([
				EditAction::make()
					->iconButton(),
				DeleteAction::make()
					->iconButton(),
			]);
	}
}
