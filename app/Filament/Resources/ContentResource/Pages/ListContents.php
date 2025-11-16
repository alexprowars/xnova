<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListContents extends ListRecords
{
	protected static string $resource = ContentResource::class;

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
			->recordActions([
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
