<?php

namespace App\Filament\Resources\PlanetResource\Pages;

use App\Filament\Resources\PlanetResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ListPlanets extends ListRecords
{
	protected static string $resource = PlanetResource::class;

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
			->columns([
				TextColumn::make('id')
					->label('ID')
					->numeric()
					->sortable()
					->searchable(),
				TextColumn::make('name')
					->label(__('admin.common.title'))
					->searchable(),
				TextColumn::make('user.username')
					->label(__('admin.common.player'))
					->numeric()
					->sortable(),
				TextColumn::make('galaxy')
					->label(__('admin.common.galaxy_short'))
					->numeric()
					->sortable(),
				TextColumn::make('system')
					->label(__('admin.common.system_short'))
					->numeric()
					->sortable(),
				TextColumn::make('planet')
					->label(__('admin.common.planet_short'))
					->numeric()
					->sortable(),
				TextColumn::make('planet_type')
					->label(__('admin.common.type'))
					->sortable(),
				TextColumn::make('last_update')
					->label(__('admin.planets.updated_at'))
					->dateTime()
					->sortable(),
				TextColumn::make('last_active')
					->label(__('admin.common.activity'))
					->dateTime()
					->sortable(),
				TextColumn::make('metal')
					->label(__('admin.planets.metal'))
					->numeric()
					->sortable(),
				TextColumn::make('crystal')
					->label(__('admin.planets.crystal'))
					->numeric()
					->sortable(),
				TextColumn::make('deuterium')
					->label(__('admin.planets.deuterium'))
					->numeric()
					->sortable(),
				TextColumn::make('debris_metal')
					->label(__('admin.planets.debris_metal_short'))
					->numeric()
					->sortable(),
				TextColumn::make('debris_crystal')
					->label(__('admin.planets.debris_crystal_short'))
					->numeric()
					->sortable(),
			])
			->filters([
				SelectFilter::make('user_id')
					->label(__('admin.common.player'))
					->relationship('user', 'username')
					->native(false)
					->searchable(['id', 'username', 'email']),
			])
			->recordActions([
				ViewAction::make()
					->iconButton(),
				EditAction::make()
					->iconButton(),
			])
			->toolbarActions([
				BulkActionGroup::make([
					DeleteBulkAction::make(),
				]),
			]);
	}
}
