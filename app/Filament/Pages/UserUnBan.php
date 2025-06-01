<?php

namespace App\Filament\Pages;

use App\Models\Blocked;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;

/**
 * @property Form $form
 */
class UserUnBan extends Page
{
	use InteractsWithForms;
	use InteractsWithFormActions;

	protected static ?string $navigationIcon = 'heroicon-o-user-plus';
	protected static ?int $navigationSort = 30;
	protected static ?string $slug = 'unban';
	protected static ?string $title = 'Разблокировать пользователя';

	protected static string $view = 'filament.pages.user-unban';

	public ?string $username = '';

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

	public function form(Form $form): Form
	{
		return $form
			->schema([
				TextInput::make('username')
					->label('Логин/email игрока')
					->required()
					->maxLength(50),
			]);
	}

	public function getFormActions(): array
	{
		return [
			Action::make('Разблокировать')
				->action(function () {
					$this->submit();
				})
		];
	}

	public function submit()
	{
		$user = User::query()->where('username', $this->username)
			->orWhere('email', $this->username)
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
