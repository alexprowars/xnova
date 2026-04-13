<?php

namespace App\Http\Controllers\Fleet;

use App\Engine\Enums\MessageType;
use App\Engine\Fleet\MissionType;
use App\Engine\Messages\Types\AcsRequestMessage;
use App\Exceptions\Exception;
use App\Http\Controllers\Controller;
use App\Models\Assault;
use App\Models\Fleet;
use App\Models\Friend;
use App\Models\Planet;
use App\Models\User;
use App\Notifications\SystemMessage;
use Illuminate\Http\Request;
use Throwable;

class FleetVerbandController extends Controller
{
	public function index(int $id): array
	{
		$fleet = $this->getFleet($id);

		$assault = $fleet->assault;

		$result = [
			'fleetid' => $fleet->id,
			'assault' => null,
			'items' => [],
		];

		if ($assault) {
			$result['assault'] = $assault->only(['id', 'name', 'fleet_id']);
		}

		if (!$assault) {
			$fleets = Fleet::query()->where('id', $fleet->id)->get();
		} else {
			$fleets = Fleet::query()->whereBelongsTo($assault)->get();
		}

		foreach ($fleets as $item) {
			$result['items'][] = [
				'id' => $item->id,
				'mission' => $item->mission,
				'amount' => $item->entities->getTotal(),
				'units' => $fleet->entities,
				'start' => [
					...$item->getOriginCoordinates()->toArray(),
					'time' => $item->start_date?->utc()->toAtomString(),
					'name' => $item->user_name,
				],
				'target' => [
					...$item->getDestinationCoordinates()->toArray(),
					'time' => $item->end_date?->utc()->toAtomString(),
					'name' => $item->target_user_name,
				],
			];
		}

		if ($fleet->id == $assault?->fleet_id) {
			$assault->loadMissing(['users', 'users.user']);

			$result['users'] = [];

			foreach ($assault->users as $user) {
				$result['users'][] = $user->user->username;
			}

			$result['alliance'] = [];

			if ($this->user->alliance_id) {
				$allianceUsers = User::query()->where('alliance_id', $this->user->alliance_id)
					->whereNot('id', $this->user->id)
					->get();

				foreach ($allianceUsers as $user) {
					$result['alliance'][] = [
						'id' => $user->id,
						'username' => $user->username,
					];
				}
			}

			$result['friends'] = [];

			$friends = Friend::query()->whereBelongsTo($this->user)
				->where('active', true)
				->with('friend')
				->get();

			foreach ($friends as $friend) {
				$result['friends'][] = $friend->only(['friend.id', 'friend.username']);
			}
		}

		return $result;
	}

	public function create(int $fleetId, Request $request): void
	{
		$fleet = $this->getFleet($fleetId);

		if ($fleet->assault_id) {
			throw new Exception('Для этого флота уже задана ассоциация!');
		}

		try {
			$assault = Assault::create([
				'name' 			=> $request->post('name', 'ACS'),
				'fleet_id' 		=> $fleet->id,
				'galaxy' 		=> $fleet->end_galaxy,
				'system' 		=> $fleet->end_system,
				'planet' 		=> $fleet->end_planet,
				'planet_type' 	=> $fleet->end_type,
				'user_id' 		=> $this->user->id,
			]);
		} catch (Throwable) {
			throw new Exception('Невозможно получить идентификатор САБ атаки');
		}

		$assault->users()->create([
			'user_id' => $this->user->id,
		]);

		$fleet->assault_id = $assault->id;
		$fleet->update();
	}

	public function user(int $fleetId, Request $request): void
	{
		$fleet = $this->getFleet($fleetId);

		if (!$fleet->assault_id) {
			throw new Exception('Для этого флота не задана ассоциация!');
		}

		$assault = $fleet->assault;

		if ($assault->fleet_id != $fleet->id) {
			throw new Exception("Вы не можете добавлять сюда игроков");
		}

		$user = null;

		$byUserId = (int) $request->post('user_id', 0);

		if ($byUserId > 0) {
			$user = User::find($request->post('user_id'));
		}

		$byName = trim($request->post('user_name', ''));

		if (!empty($byName)) {
			$user = User::whereUsername($byName)->first();
		}

		if (!$user) {
			throw new Exception('Игрок не найден');
		}

		$assaultUser = $assault->users()
			->whereBelongsTo($user)
			->first();

		if ($assaultUser) {
			throw new Exception('Игрок уже приглашён для нападения');
		}

		$assault->users()->create([
			'user_id' => $user->id,
		]);

		$planet = Planet::findByCoordinates($assault->coordinates);

		$message = new AcsRequestMessage([
			'assault' => $assault->name,
			'user' => $this->user->username,
			'planet' => [
				'name' => $planet->name,
				'user' => $planet->user->username,
				...$planet->coordinates->toArray(),
			]
		]);

		$user->notify(new SystemMessage(MessageType::User, $message));
	}

	public function name(int $fleetId, Request $request): void
	{
		$fleet = $this->getFleet($fleetId);

		if (!$fleet->assault_id) {
			throw new Exception('Для этого флота не задана ассоциация!');
		}

		$assault = $fleet->assault;

		if ($assault->fleet_id != $fleet->id) {
			throw new Exception('Вы не можете менять имя ассоциации');
		}

		$name = strip_tags($request->post('name'));

		if (mb_strlen($name) < 5) {
			throw new Exception('Слишком короткое имя ассоциации');
		}

		if (mb_strlen($name) > 20) {
			throw new Exception('Слишком длинное имя ассоциации');
		}

		if (!preg_match("/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u", $name)) {
			throw new Exception('Имя ассоциации содержит запрещённые символы');
		}

		$exist = Assault::where('name', $name)->exists();

		if ($exist) {
			throw new Exception('Имя уже зарезервировано другим игроком');
		}

		$assault->name = $name;
		$assault->save();
	}

	protected function getFleet(int $id): Fleet
	{
		if ($id <= 0) {
			throw new Exception('Флот не выбран');
		}

		$fleet = Fleet::query()
			->whereBelongsTo($this->user)
			->where('mission', MissionType::Attack)
			->findOne($id);

		if (!$fleet) {
			throw new Exception('Этот флот не существует!');
		}

		if ($fleet->start_date->isPast() || $fleet->end_date->isPast() || $fleet->mess == 1) {
			throw new Exception('Ваш флот возвращается на планету!');
		}

		return $fleet;
	}
}
