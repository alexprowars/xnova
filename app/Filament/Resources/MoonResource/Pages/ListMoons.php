<?php

namespace App\Filament\Resources\MoonResource\Pages;

use App\Engine\Enums\PlanetType;
use App\Filament\Resources\MoonResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class ListMoons extends ListRecords
{
	protected static string $resource = MoonResource::class;

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
			->modifyQueryUsing(fn (Builder $query) => $query->where('planet_type', PlanetType::MOON))
			->emptyStateHeading('Не найдены луны')
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
				TextColumn::make('last_active')
					->label('Активность')
					->dateTime()
					->sortable(),
			])
			->filters([
				SelectFilter::make('user_id')
					->label('Игрок')
					->relationship('user', 'username')
					->native(false)
					->searchable(['id', 'username', 'email'])
			])
			->actions([
				Tables\Actions\EditAction::make()
					->iconButton()
			])
			->bulkActions([
				Tables\Actions\BulkActionGroup::make([
					Tables\Actions\DeleteBulkAction::make(),
				]),
			]);
	}
}
