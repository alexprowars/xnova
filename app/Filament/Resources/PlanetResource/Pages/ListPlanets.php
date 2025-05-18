<?php

namespace App\Filament\Resources\PlanetResource\Pages;

use App\Filament\Resources\PlanetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables;

class ListPlanets extends ListRecords
{
	protected static string $resource = PlanetResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Actions\CreateAction::make(),
		];
	}

	public function table(Table $table): Table
	{
		return $table
			->defaultSort('id', 'desc')
			->columns([
				TextColumn::make('id')
					->label('ID')
					->numeric()
					->sortable()
					->searchable(),
				TextColumn::make('name')
					->label('Название')
					->searchable(),
				TextColumn::make('user.username')
					->label('Игрок')
					->numeric()
					->sortable(),
				TextColumn::make('galaxy')
					->label('Г')
					->numeric()
					->sortable(),
				TextColumn::make('system')
					->label('C')
					->numeric()
					->sortable(),
				TextColumn::make('planet')
					->label('П')
					->numeric()
					->sortable(),
				TextColumn::make('planet_type')
					->label('Тип')
					->sortable(),
				TextColumn::make('last_update')
					->label('Время обновления')
					->dateTime()
					->sortable(),
				TextColumn::make('last_active')
					->label('Активность')
					->dateTime()
					->sortable(),
				TextColumn::make('metal')
					->label('Металл')
					->numeric()
					->sortable(),
				TextColumn::make('crystal')
					->label('Кристалл')
					->numeric()
					->sortable(),
				TextColumn::make('deuterium')
					->label('Дейтерий')
					->numeric()
					->sortable(),
				TextColumn::make('debris_metal')
					->label('Обл. металла')
					->numeric()
					->sortable(),
				TextColumn::make('debris_crystal')
					->label('Обл. кристалла')
					->numeric()
					->sortable(),
			])
			->filters([
				SelectFilter::make('user_id')
					->label('Игрок')
					->relationship('user', 'username')
					->native(false)
					->searchable(['id', 'username', 'email']),
			])
			->actions([
				Tables\Actions\ViewAction::make()
					->iconButton(),
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
