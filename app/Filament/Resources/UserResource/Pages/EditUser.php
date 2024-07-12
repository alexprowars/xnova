<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditUser extends EditRecord
{
	protected static string $resource = UserResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Actions\DeleteAction::make(),
		];
	}

	public function form(Form $form): Form
	{
		return $form
			->columns(1)
			->schema([
				Select::make('roles')->label('Роли')
					->multiple()
					->relationship('roles', 'name')
			]);
	}
}
