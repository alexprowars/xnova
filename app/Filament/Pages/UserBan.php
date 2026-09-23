<?php

namespace App\Filament\Pages;

use App\Facades\Vars;
use App\Filament\HasPageForm;
use App\Models\Blocked;
use App\Models\Planet;
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
	public ?array $data = [];

	public function getTitle(): string
	{
		return __('admin.user_ban.ban_user');
	}

	public static function getNavigationIcon(): string
	{
		return 'heroicon-o-user-minus';
	}

	public static function getNavigationGroup(): string
	{
		return __('admin.groups.management');
	}

	public static function getNavigationLabel(): string
	{
		return __('admin.pages.user_ban');
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
							->label(__('admin.common.player_login_or_email'))
							->required()
							->maxLength(50),
						TextInput::make('reason')
							->label(__('admin.user_ban.reason'))
							->maxLength(50),
						Fieldset::make(__('admin.user_ban.ban_duration'))
							->schema([
								TextInput::make('days')->integer()->label(__('admin.user_ban.days')),
								TextInput::make('hour')->integer()->label(__('admin.user_ban.hours')),
								TextInput::make('mins')->integer()->label(__('admin.user_ban.minutes')),
							])
							->columns(3),
						Checkbox::make('vacation')
							->label(__('admin.user_ban.vacation_mode'))
							->default(false),
					]),
			])
			->statePath('data');
	}

	public function getFormActions(): array
	{
		return [
			Action::make('ban')->label(__('admin.user_ban.ban'))
				->action(function () {
					$this->submit($this->form->getState());
				})
		];
	}

	protected function submit(array $data): void
	{
		$user = User::query()->where('username', $data['username'])
			->orWhere('email', $data['username'])
			->first();

		if (!$user) {
			Notification::make()
				->title(__('admin.common.player_not_found'))
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

			$user->planets->each(function (Planet $planet) use ($buildsId) {
				$planet->entities->whereIn('entity_id', $buildsId)
					->each(function (PlanetEntity $entity) {
						$entity->setFactor(0);
						$entity->save();
					});
			});
		}

		Notification::make()
			->title(__('admin.user_ban.player_banned', ['name' => $user->username]))
			->success()->send();

		$this->form->fill();
	}
}
