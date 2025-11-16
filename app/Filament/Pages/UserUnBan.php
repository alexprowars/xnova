<?php

namespace App\Filament\Pages;

use App\Filament\HasPageForm;
use App\Models\Blocked;
use App\Models\User;
use Filament\Actions\Action;
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
class UserUnBan extends Page
{
	use InteractsWithForms;
	use InteractsWithFormActions;
	use HasPageForm;

	protected static ?int $navigationSort = 30;
	protected static ?string $slug = 'unban';
	protected static ?string $title = 'Разблокировать пользователя';

	public ?array $data = [];

	public static function getNavigationIcon(): string
	{
		return 'heroicon-o-user-plus';
	}

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.management');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.user_unban');
	}

	public static function canAccess(): bool
	{
		return auth()->user()->can('users-unblock');
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
						TextInput::make('username')
							->label('Логин/email игрока')
							->required()
							->maxLength(50),
				]),
			])
			->statePath('data');
	}

	public function getFormActions(): array
	{
		return [
			Action::make('Разблокировать')
				->action(function () {
					$this->submit($this->form->getState());
				})
		];
	}

	protected function submit(array $data)
	{
		$user = User::query()->where('username', $data['username'])
			->orWhere('email', $data['username'])
			->first();

		if (!$user) {
			Notification::make()
				->title('Игрок не найден')
				->danger()->send();

			return;
		}

		Blocked::query()->whereBelongsTo($user)->delete();

		$user->blocked_at = null;

		if ($user->vacation?->timestamp == 0) {
			$user->vacation = null;
		}

		$user->save();

		Notification::make()
			->title('Игрок "' . $user->username . '" разбанен!')
			->success()->send();

		$this->form->fill();
	}
}
