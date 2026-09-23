<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateUser extends CreateRecord
{
	protected static string $resource = UserResource::class;

	public function getTitle(): string
	{
		return __('admin.users.create_user');
	}

	public function form(Schema $schema): Schema
	{
		return $schema
			->columns(1)
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
					->password()
					->required(),
			]);
	}

	protected function handleRecordCreation(array $data): User
	{
		return UserService::creation([
			'name' => $data['username'],
			'email' => $data['email'],
			'password' => $data['password'],
		], true);
	}

	protected function getCreatedNotificationTitle(): ?string
	{
		return __('admin.users.user_created');
	}
}
