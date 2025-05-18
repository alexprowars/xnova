<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class ListContents extends ListRecords
{
	protected static string $resource = ContentResource::class;

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
			->emptyStateHeading('Контент не найден')
			->columns([
				TextColumn::make('id')
					->label('ID')
					->sortable(),
				TextColumn::make('title')
					->label('Название')
					->sortable(),
				TextColumn::make('alias')
					->label('Символьный код')
					->sortable(),
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
