<?php

namespace App\Filament\Pages;

use App\Engine\Vars;
use App\Models\Blocked;
use App\Models\PlanetEntity;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Date;

/**
 * @property Form $form
 */
class UserBan extends Page
{
	use InteractsWithForms;
	use InteractsWithFormActions;

	protected static ?string $navigationIcon = 'heroicon-o-user-minus';
	protected static ?string $navigationLabel = 'Заблокировать';
	protected static ?string $navigationGroup = 'Администрирование';
	protected static ?int $navigationSort = 20;
	protected static ?string $slug = 'ban';
	protected static ?string $title = 'Заблокировать пользователя';

	protected static string $view = 'filament.pages.user-ban';

	public ?string $username = '';
	public ?string $reason = '';
	public ?string $days = '';
	public ?string $hour = '';
	public ?string $mins = '';
	public bool $vacation = false;

	protected function getFormSchema(): array
	{
		return [
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
		];
	}

	public function getFormActions(): array
	{
		return [
			Action::make('Заблокировать')
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

		$BanTime = now()->addDays((int) $this->days)
			->addHours((int) $this->hour)
			->addMinutes((int) $this->mins);

		Blocked::create([
			'user_id'	=> $user->id,
			'reason'	=> $this->reason,
			'longer'	=> $BanTime,
			'author_id'	=> auth()->id(),
		]);

		$update = ['blocked_at' => $BanTime];

		if ($this->vacation) {
			$update['vacation'] = Date::createFromTimestamp(0);
		}

		$user->update($update);

		if ($this->vacation) {
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
