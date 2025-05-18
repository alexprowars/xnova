<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
				Section::make()
					->schema([
						TextInput::make('username')
							->label('Юзернэйм')
							->maxLength(50)
							->required(),
						TextInput::make('email')
							->label('Email')
							->maxLength(50)
							->email()
							->required(),
						TextInput::make('password')
							->label('Пароль')
							->password(),
						Select::make('race')
							->label('Раса')
							->options(__('main.race')),
						TextInput::make('credits')
							->label('Кредиты')
							->integer(),
						Textarea::make('about')
							->label('О себе')
							->rows(5),
						Select::make('roles')->label('Роли')
							->multiple()
							->relationship('roles', 'name')
							->native(false),
					]),
				Section::make()
					->heading('Офицеры')
					->schema([
						DateTimePicker::make('rpg_geologue')
							->label(__('main.tech.601')),
						DateTimePicker::make('rpg_admiral')
							->label(__('main.tech.602')),
						DateTimePicker::make('rpg_ingenieur')
							->label(__('main.tech.603')),
						DateTimePicker::make('rpg_technocrate')
							->label(__('main.tech.604')),
						DateTimePicker::make('rpg_constructeur')
							->label(__('main.tech.605')),
						DateTimePicker::make('rpg_meta')
							->label(__('main.tech.606')),
						DateTimePicker::make('rpg_komandir')
							->label(__('main.tech.607')),
					])
			]);
	}
}
