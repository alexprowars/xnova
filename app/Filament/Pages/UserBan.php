<?php

namespace App\Filament\Pages;

use App\Facades\Vars;
use App\Filament\HasPageForm;
use App\Models\Blocked;
use App\Models\PlanetEntity;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Date;

/**
 * @property Schema $form
 */
class UserBan extends Page
{
	use InteractsWithForms;
	use InteractsWithFormActions;
	use HasPageForm;

	protected static ?int $navigationSort = 20;
	protected static ?string $slug = 'ban';
	protected static ?string $title = 'Заблокировать пользователя';

	public ?array $data = [];

	public static function getNavigationIcon(): string
	{
		return 'heroicon-o-user-minus';
	}

	public static function getNavigationGroup(): string
	{
		return __('admin.navigation.groups.management');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.navigation.pages.user_ban');
	}

	public static function canAccess(): bool
	{
		return auth()->user()->can('users-block');
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
						TextInput::make('reason')
							->label('Причина')
							->maxLength(50),
						Fieldset::make('Время бана')
							->schema([
								TextInput::make('days')->integer()->label('дней'),
								TextInput::make('hour')->integer()->label('часов'),
								TextInput::make('mins')->integer()->label('минут'),
							])
							->columns(3),
						Checkbox::make('vacation')
							->label('Режим отпуска')
							->default(false),
					]),
			])
			->statePath('data');
	}

	public function getFormActions(): array
	{
		return [
			Action::make('Заблокировать')
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

		$BanTime = now()->addDays((int) $data['days'])
			->addHours((int) $data['hour'])
			->addMinutes((int) $data['mins']);

		Blocked::create([
			'user_id'	=> $user->id,
			'reason'	=> $data['reason'],
			'longer'	=> $BanTime,
			'author_id'	=> auth()->id(),
		]);

		$update = ['blocked_at' => $BanTime];

		if ($data['vacation']) {
			$update['vacation'] = Date::createFromTimestamp(0);
		}

		$user->update($update);

		if ($data['vacation']) {
			$buildsId = [4, 12, 212];

			foreach (Vars::getResources() as $res) {
				$buildsId[] = Vars::getIdByName($res . '_mine');
			}

			PlanetEntity::query()->whereIn('planet_id', User::getPlanetsId($user))
				->whereIn('entity_id', $buildsId)
				->update(['factor' => 0]);
		}

		Notification::make()
			->title('Игрок "' . $user->username . '" заблокирован!')
			->success()->send();

		$this->form->fill();
	}
}
