<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables;

class ListUsers extends ListRecords
{
	protected static string $resource = UserResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Actions\CreateAction::make(),
		];
	}

	public function table(Table $table): Table
	{
		return $table
			->columns([
				TextColumn::make('id')
					->label('ID')
					->numeric()
					->sortable()
					->searchable(),
				TextColumn::make('email')
					->label('Email'),
				TextColumn::make('username')
					->label('Никнейм'),
				TextColumn::make('galaxy')
					->label('Г'),
				TextColumn::make('system')
					->label('С'),
				TextColumn::make('planet')
					->label('П'),
				TextColumn::make('created_at')
					->label('Дата регистрации')
					->dateTime('d.m.Y H:i:s'),
			])
			->actions([
				Tables\Actions\ViewAction::make(),
				Tables\Actions\EditAction::make(),
			]);
	}
}
