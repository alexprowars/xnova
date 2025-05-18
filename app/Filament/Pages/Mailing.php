<?php

namespace App\Filament\Pages;

use App\Engine\Enums\MessageType;
use App\Models\User;
use App\Notifications\MessageNotification;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;

/**
 * @property Form $form
 */
class Mailing extends Page
{
	use InteractsWithForms;
	use InteractsWithFormActions;

	protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
	protected static ?int $navigationSort = 120;
	protected static ?string $slug = 'mailing';

	protected static string $view = 'filament.pages.mailing';

	public ?string $theme = '';
	public ?string $message = '';

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.game');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.mailing');
	}

	public function getTitle(): string
	{
		return 'Отправить сообщение всем игрокам';
	}

	public function form(Form $form): Form
	{
		return $form
			->schema([
				TextInput::make('theme')
					->label('Тема сообщения')
					->maxLength(50),
				Textarea::make('message')
					->label('Сообщение')
					->required()
					->rows(10),
			]);
	}

	public function getFormActions(): array
	{
		return [
			Action::make('Отправить')
				->action(function () {
					$this->submit();
				})
		];
	}

	public function submit()
	{
		$currentUser = auth()->user();

		if ($currentUser->isAdmin()) {
			$color = 'yellow';
		} else {
			$color = 'skyblue';
		}

		$users = User::query()->pluck('id');

		foreach ($users as $user) {
			$user->notify(new MessageNotification(
				null,
				MessageType::System,
				$this->theme ?: '<font color="' . $color . '">Информационное сообщение (' . $currentUser->username . ')</font>',
				$this->message
			));
		}

		Notification::make()
			->title('Сообщение успешно отправлено всем игрокам!')
			->success()->send();

		$this->form->fill();
	}
}
