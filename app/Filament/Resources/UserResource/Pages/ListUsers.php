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
					->label(__('admin.users.nickname')),
				TextColumn::make('galaxy')
					->label(__('admin.common.galaxy_short')),
				TextColumn::make('system')
					->label(__('admin.common.system_short')),
				TextColumn::make('planet')
					->label(__('admin.common.planet_short')),
				TextColumn::make('ip')
					->label('IP'),
				TextColumn::make('created_at')
					->label(__('admin.users.registered_at'))
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
