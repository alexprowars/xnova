<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListUsers extends ListRecords
{
	protected static string $resource = UserResource::class;

	protected function getHeaderActions(): array
	{
		return [
			CreateAction::make(),
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
				TextColumn::make('ip')
					->label('IP')
					->formatStateUsing(fn ($state) => long2ip($state)),
				TextColumn::make('created_at')
					->label('Дата регистрации')
					->dateTime(),
			])
			->filters([])
			->recordActions([
				ViewAction::make()
					->iconButton(),
				EditAction::make()
					->iconButton(),
			]);
	}
}
