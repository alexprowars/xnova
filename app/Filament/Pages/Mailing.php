<?php

namespace App\Filament\Pages;

use App\Engine\Enums\MessageType;
use App\Filament\HasPageForm;
use App\Models\User;
use App\Notifications\MessageNotification;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * @property Schema $form
 */
class Mailing extends Page
{
	use InteractsWithForms;
	use InteractsWithFormActions;
	use HasPageForm;

	protected static ?int $navigationSort = 120;
	protected static ?string $slug = 'mailing';

	public ?array $data = [];

	public static function getNavigationIcon(): string
	{
		return 'heroicon-o-envelope-open';
	}

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

	public static function canAccess(): bool
	{
		return auth()->user()->can('mailing');
	}

	public function mount(): void
	{
		$this->form->fill();
	}

	public function form(Schema $schema): Schema
	{
		return $schema
			->components([
				Section::make()
					->compact()
					->schema([
						TextInput::make('subject')
							->label('Тема сообщения')
							->maxLength(50),
						Textarea::make('message')
							->label('Сообщение')
							->required()
							->rows(10),
				]),
			])
			->statePath('data');
	}

	protected function getFormActions(): array
	{
		return [
			Action::make('Отправить')
				->action(function () {
					$this->submit($this->form->getState());
				})
		];
	}

	protected function submit(array $data)
	{
		$currentUser = auth()->user();

		if ($currentUser->isAdmin()) {
			$color = 'yellow';
		} else {
			$color = 'skyblue';
		}

		$users = User::query()->get();

		foreach ($users as $user) {
			$user->notify(new MessageNotification(
				null,
				MessageType::System,
				$data['subject'] ?: '<span style="color: ' . $color . '">Информационное сообщение (' . $currentUser->username . ')</span>',
				$data['message']
			));
		}

		Notification::make()
			->success()
			->title('Сообщение успешно отправлено всем игрокам!')
			->send();

		$this->form->fill();
	}
}
