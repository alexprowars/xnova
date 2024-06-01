<?php

namespace App\Http\Controllers\Fleet;

use App\Engine\Fleet\Mission;
use App\Exceptions\ErrorException;
use App\Http\Controllers\Controller;
use App\Models\Assault;
use App\Models\Fleet;
use App\Models\Friend;
use App\Models\Planet;
use App\Models\User;
use Illuminate\Http\Request;

class FleetVerbandController extends Controller
{
	public function index(Request $request, $fleetId)
	{
		$fleetId = (int) $fleetId;

		if ($fleetId <= 0) {
			throw new ErrorException('Флот не выбран');
		}

		$fleet = Fleet::query()
			->where('id', $fleetId)
			->where('user_id', $this->user->id)
			->where('mission', Mission::Attack)
			->first();

		if (!$fleet) {
			throw new ErrorException('Этот флот не существует!');
		}

		if ($fleet->start_time->getTimestamp() <= time() || $fleet->end_time->timestamp < time() || $fleet->mess == 1) {
			throw new ErrorException('Ваш флот возвращается на планету!');
		}

		$assault = $fleet->assault;

		if ($request->has('action')) {
			$action = $request->post('action');

			if ($action == 'add') {
				if ($fleet->assault_id) {
					throw new ErrorException('Для этого флота уже задана ассоциация!');
				}

				$assault = Assault::create([
					'name' 			=> $request->post('name', 'string'),
					'fleet_id' 		=> $fleet->id,
					'galaxy' 		=> $fleet->end_galaxy,
					'system' 		=> $fleet->end_system,
					'planet' 		=> $fleet->end_planet,
					'planet_type' 	=> $fleet->end_type,
					'user_id' 		=> $this->user->id,
				]);

				if (!$assault) {
					throw new ErrorException('Невозможно получить идентификатор САБ атаки');
				}

				$assault->users()->create([
					'user_id' => $this->user->id,
				]);

				$fleet->assault_id = $assault->id;
				$fleet->update();
			} elseif ($action == 'adduser') {
				if ($assault->fleet_id != $fleet->id) {
					throw new ErrorException("Вы не можете добавлять сюда игроков");
				}

				$user_data = false;

				$byId = (int) $request->post('user_id', 'int');

				if ($byId > 0) {
					$user_data = User::find($request->post('user_id'));
				}

				$byName = trim($request->post('user_name', 'string'));

				if ($byName != '') {
					$user_data = User::whereUsername($byName)->first();
				}

				if (!$user_data) {
					throw new ErrorException("Игрок не найден");
				}

				$assaultUser = $assault->users()->where('user_id', $user_data->id)->first();

				if ($assaultUser) {
					throw new ErrorException("Игрок уже приглашён для нападения");
				}

				$assault->users()->create([
					'user_id' => $user_data->id,
				]);

				$planet = Planet::query()
					->where('galaxy', $assault->galaxy)
					->where('system', $assault->system)
					->where('planet', $assault->planet)
					->where('planet_type', $assault->planet_type)
					->first();

				$message = "Игрок " . $this->user->username . " приглашает вас произвести совместное нападение на планету " . $planet->name . " [" . $assault->galaxy . ":" . $assault->system . ":" . $assault->planet . "] игрока " . $planet->user->username . ". Имя ассоциации: " . $assault->name . ". Если вы отказываетесь, то просто проигнорируйте данной сообщение.";

				User::sendMessage($user_data->id, false, 0, 1, 'Флот', $message);
			} elseif ($action == "changename") {
				if ($assault->fleet_id != $fleet->id) {
					throw new ErrorException("Вы не можете менять имя ассоциации");
				}

				$name = strip_tags($request->post('name', 'string'));

				if (mb_strlen($name) < 5) {
					throw new ErrorException("Слишком короткое имя ассоциации");
				}

				if (mb_strlen($name) > 20) {
					throw new ErrorException("Слишком длинное имя ассоциации");
				}

				if (!preg_match("/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u", $name)) {
					throw new ErrorException("Имя ассоциации содержит запрещённые символы");
				}

				$x = Assault::where('name', $name)->exists();

				if ($x) {
					throw new ErrorException("Имя уже зарезервировано другим игроком");
				}

				$assault->name = $name;
				$assault->save();
			}
		}

		if (!$fleet->assault_id) {
			$fq = Fleet::query()->where('id', $fleet->id)->get();
		} else {
			$fq = Fleet::query()->where('assault_id', $fleet->assault_id)->get();
		}

		if ($assault) {
			$assault->fleet_id = (int) $assault->fleet_id;
		}

		$parse = [];
		$parse['group'] = (int) $fleet->assault_id;
		$parse['fleetid'] = $fleet->id;
		$parse['aks'] = $assault->toArray();
		$parse['list'] = [];

		foreach ($fq as $row) {
			$parse['list'][] = [
				'id' => $row->id,
				'ships' => $row->getShips(),
				'ships_total' => $row->getTotalShips(),
				'mission' => $row->mission,
				'start' => [
					'galaxy' => $row->start_galaxy,
					'system' => $row->start_system,
					'planet' => $row->start_planet,
					'time' => $row->start_time?->utc()->toAtomString(),
					'name' => $row->user_name,
				],
				'target' => [
					'galaxy' => $row->end_system,
					'system' => $row->end_system,
					'planet' => $row->end_planet,
					'time' => $row->end_time?->utc()->toAtomString(),
					'name' => $row->target_user_name,
				],
			];
		}

		if ($fleet->id == $assault->fleet_id) {
			$assault->load(['users', 'users.user']);

			$parse['users'] = [];

			foreach ($assault->users as $user) {
				$parse['users'][] = $user->user->username;
			}

			$parse['alliance'] = [];

			if ($this->user->alliance_id) {
				$allianceUsers = User::query()->where('alliance_id', $this->user->alliance_id)
					->whereNot('id', $this->user->id)
					->get();

				foreach ($allianceUsers as $user) {
					$parse['alliance'][] = [
						'id' => $user->id,
						'username' => $user->username,
					];
				}
			}

			$parse['friends'] = [];

			$friends = Friend::query()->where('user_id', $this->user->id)
				->where('active', true)
				->with('friend')
				->get();

			foreach ($friends as $friend) {
				$parse['friends'][] = $friend->only(['friend.id', 'friend.username']);
			}
		}

		return response()->state($parse);
	}
}
