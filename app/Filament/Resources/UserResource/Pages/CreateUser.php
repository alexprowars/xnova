<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Exceptions\Exception;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateUser extends CreateRecord
{
	protected static string $resource = UserResource::class;
	protected static ?string $title = 'Создать пользователя';

	public function form(Schema $schema): Schema
	{
		return $schema
			->columns(1)
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
					->password()
					->required(),
			]);
	}

	protected function handleRecordCreation(array $data): User
	{
		$user = User::creation([
			'name' => $data['username'],
			'email' => $data['email'],
			'password' => $data['password'],
		]);

		if (!$user) {
			throw new Exception('Не удалось создать пользователя');
		}

		return $user;
	}

	protected function getCreatedNotificationTitle(): ?string
	{
		return 'Пользователь создан';
	}
}
