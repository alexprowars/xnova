<?php

namespace App\Filament\Resources\AlliancesResource\Pages;

use App\Filament\Resources\AlliancesResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class ListAlliance extends ListRecords
{
	protected static string $resource = AlliancesResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Actions\CreateAction::make(),
		];
	}

	public function table(Table $table): Table
	{
		return $table
			->modifyQueryUsing(function (Builder $query) {
				return $query->with(['user']);
			})
			->defaultSort('id', 'desc')
			->emptyStateHeading('Альянсы не найдены')
			->columns([
				TextColumn::make('id')
					->label('ID')
					->sortable(),
				TextColumn::make('name')
					->label('Имя')
					->searchable(),
				TextColumn::make('tag')
					->label('Тэг')
					->searchable(),
				TextColumn::make('user')
					->label('Лидер')
					->formatStateUsing(function (User $state) {
						return $state->username . ($state->galaxy ? ' [' . $state->galaxy . ':' . $state->system . ':' . $state->planet . ']' : '');
					}),
				TextColumn::make('members_count')
					->label('Кол-во участников'),
				TextColumn::make('created_at')
					->label('Дата создания')
					->dateTime(),
			])
			->actions([
				Tables\Actions\EditAction::make()
					->iconButton(),
			]);
	}
}
