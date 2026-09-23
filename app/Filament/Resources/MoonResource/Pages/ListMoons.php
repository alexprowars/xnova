<?php

namespace App\Filament\Resources\MoonResource\Pages;

use App\Engine\Enums\PlanetType;
use App\Filament\Resources\MoonResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListMoons extends ListRecords
{
	protected static string $resource = MoonResource::class;

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
			->modifyQueryUsing(fn (Builder $query) => $query->where('planet_type', PlanetType::MOON))
			->emptyStateHeading(__('admin.moons.moons_not_found'))
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
				TextColumn::make('last_active')
					->label(__('admin.common.activity'))
					->dateTime()
					->sortable(),
			])
			->filters([
				SelectFilter::make('user_id')
					->label(__('admin.common.player'))
					->relationship('user', 'username')
					->native(false)
					->searchable(['id', 'username', 'email'])
			])
			->recordActions([
				EditAction::make()
					->iconButton()
			])
			->toolbarActions([
				BulkActionGroup::make([
					DeleteBulkAction::make(),
				]),
			]);
	}
}
