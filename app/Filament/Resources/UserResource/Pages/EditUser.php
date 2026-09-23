<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditUser extends EditRecord
{
	protected static string $resource = UserResource::class;

	protected function getHeaderActions(): array
	{
		return [
			DeleteAction::make(),
		];
	}

	public function form(Schema $schema): Schema
	{
		return $schema
			->columns(1)
			->schema([
				Section::make()
					->schema([
						TextInput::make('username')
							->label(__('admin.users.username'))
							->maxLength(50)
							->required(),
						TextInput::make('email')
							->label('Email')
							->maxLength(50)
							->email()
							->required(),
						TextInput::make('password')
							->label(__('admin.users.password'))
							->password(),
						Select::make('race')
							->label(__('admin.users.race'))
							->options(__('main.race')),
						TextInput::make('credits')
							->label(__('admin.users.credits'))
							->integer(),
						Textarea::make('about')
							->label(__('admin.users.about'))
							->rows(5),
						Select::make('roles')->label(__('admin.users.roles'))
							->multiple()
							->relationship('roles', 'name')
							->native(false),
					]),
				Section::make()
					->heading(__('admin.users.officers'))
					->schema([
						DateTimePicker::make('officier_geologist')
							->label(__('main.tech.601')),
						DateTimePicker::make('officier_admiral')
							->label(__('main.tech.602')),
						DateTimePicker::make('officier_engineer')
							->label(__('main.tech.603')),
						DateTimePicker::make('officier_technocrat')
							->label(__('main.tech.604')),
						DateTimePicker::make('officier_architect')
							->label(__('main.tech.605')),
						DateTimePicker::make('officier_metaphysician')
							->label(__('main.tech.606')),
						DateTimePicker::make('officier_mercenary')
							->label(__('main.tech.607')),
					])
			]);
	}
}
