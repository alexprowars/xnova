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
					->label(__('admin.ai.strategy')),
				TextColumn::make('user.id')
					->label(__('admin.ai.player_id')),
				TextColumn::make('user.username')
					->label(__('admin.common.name')),
				TextColumn::make('user.galaxy')
					->label(__('admin.common.coordinates'))
					->formatStateUsing(function (Ai $record) {
						return $record->user->galaxy . ':' . $record->user->system . ':' . $record->user->planet;
					}),
				TextColumn::make('created_at')
					->label(__('admin.common.created_at'))
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
