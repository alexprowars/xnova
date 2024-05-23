<?php

namespace App\Http\Controllers\Fleet;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Controller;
use App\Exceptions\ErrorException;
use App\Models\Assault;
use App\Models\AssaultUser;
use App\Models\Fleet;
use App\User;

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
			->where('owner', $this->user->id)
			->where('mission', 1)
			->first();

		if (!$fleet) {
			throw new ErrorException('Этот флот не существует!');
		}

		$aks = DB::selectOne("SELECT * FROM assaults WHERE id = :id", ['id' => $fleet->group_id]);

		if ($fleet->start_time <= time() || $fleet->end_time < time() || $fleet->mess == 1) {
			throw new ErrorException('Ваш флот возвращается на планету!');
		}

		if ($request->has('action')) {
			$action = $request->post('action');

			if ($action == 'add') {
				if ($fleet->group_id) {
					throw new ErrorException('Для этого флота уже задана ассоциация!');
				}

				$aks = Assault::query()->create([
					'name' 			=> $request->post('name', 'string'),
					'fleet_id' 		=> $fleet->id,
					'galaxy' 		=> $fleet->end_galaxy,
					'system' 		=> $fleet->end_system,
					'planet' 		=> $fleet->end_planet,
					'planet_type' 	=> $fleet->end_type,
					'user_id' 		=> $this->user->id,
				]);

				if (!$aks) {
					throw new ErrorException('Невозможно получить идентификатор САБ атаки');
				}

				AssaultUser::query()->create([
					'aks_id'	=> $aks->id,
					'user_id'	=> $this->user->id
				]);

				$fleet->group_id = $aks->id;
				$fleet->update();
			} elseif ($action == 'adduser') {
				if ($aks->fleet_id != $fleet->id) {
					throw new ErrorException("Вы не можете добавлять сюда игроков");
				}

				$user_data = false;

				$byId = (int) $request->post('user_id', 'int');

				if ($byId > 0) {
					$user_data = DB::selectOne("SELECT * FROM users WHERE id = '" . $request->post('user_id', 'int') . "'");
				}

				$byName = trim($request->post('user_name', 'string'));

				if ($byName != '') {
					$user_data = DB::selectOne("SELECT * FROM users WHERE username = :name", ['name' => $byName]);
				}

				if (!$user_data) {
					throw new ErrorException("Игрок не найден");
				}

				$aks_user = DB::select("SELECT * FROM aks_user WHERE aks_id = " . $aks->id . " AND user_id = " . $user_data->id . "");

				if (count($aks_user)) {
					throw new ErrorException("Игрок уже приглашён для нападения");
				}

				AssaultUser::query()->insert([
					'aks_id' => $aks->id,
					'user_id' => $user_data->id,
				]);

				$planet_daten = DB::selectOne("SELECT `id_owner`, `name` FROM planets WHERE galaxy = '" . $aks->galaxy . "' AND system = '" . $aks->system . "' AND planet = '" . $aks->planet . "' AND planet_type = '" . $aks->planet_type . "'");
				$owner = DB::selectOne("SELECT username FROM users WHERE id = '" . $planet_daten->id_owner . "'");

				$message = "Игрок " . $this->user->username . " приглашает вас произвести совместное нападение на планету " . $planet_daten->name . " [" . $aks->galaxy . ":" . $aks->system . ":" . $aks->planet . "] игрока " . $owner->username . ". Имя ассоциации: " . $aks->name . ". Если вы отказываетесь, то просто проигнорируйте данной сообщение.";

				User::sendMessage($user_data->id, false, 0, 1, 'Флот', $message);
			} elseif ($action == "changename") {
				if ($aks->fleet_id != $fleet->id) {
					throw new ErrorException("Вы не можете менять имя ассоциации");
				}

				$name = $request->post('name', 'string');

				if (mb_strlen($name) < 5) {
					throw new ErrorException("Слишком короткое имя ассоциации");
				}

				if (mb_strlen($name) > 20) {
					throw new ErrorException("Слишком длинное имя ассоциации");
				}

				if (!preg_match("/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u", $name)) {
					throw new ErrorException("Имя ассоциации содержит запрещённые символы");
				}

				$name = strip_tags($name);

				$x = DB::select("SELECT * FROM aks WHERE name = :name", ['name' => $name]);

				if (count($x)) {
					throw new ErrorException("Имя уже зарезервировано другим игроком");
				}

				$aks->name = $name;

				Assault::query()->where('id', $aks->id)->update(['name' => $name]);
			}
		}

		if ($fleet->group_id == 0) {
			$fq = Fleet::query()->where('id', $fleet->id)->get();
		} else {
			$fq = Fleet::query()->where('group_id', $fleet->group_id)->get();
		}

		if ($aks) {
			$aks->fleet_id = (int) $aks->fleet_id;
		}

		$parse = [];
		$parse['group'] = (int) $fleet->group_id;
		$parse['fleetid'] = (int) $fleet->id;
		$parse['aks'] = (array) $aks;
		$parse['list'] = [];

		foreach ($fq as $row) {
			$parse['list'][] = [
				'id' => (int) $row->id,
				'ships' => $row->getShips(),
				'ships_total' => $row->getTotalShips(),
				'mission' => (int) $row->mission,
				'start' => [
					'galaxy' => (int) $row->start_galaxy,
					'system' => (int) $row->start_system,
					'planet' => (int) $row->start_planet,
					'time' => (int) $row->start_time,
					'name' => $row->owner_name,
				],
				'target' => [
					'galaxy' => (int) $row->end_system,
					'system' => (int) $row->end_system,
					'planet' => (int) $row->end_planet,
					'time' => (int) $row->end_time,
					'name' => $row->target_owner_name,
				],
			];
		}

		if ($fleet->id == $aks->fleet_id) {
			$parse['users'] = [];

			$query = DB::select("SELECT users.username FROM users, aks_user WHERE users.id = aks_user.user_id AND aks_user.aks_id = " . $fleet->group_id . "");

			foreach ($query as $us) {
				$parse['users'][] = $us->username;
			}

			$parse['alliance'] = [];

			if ($this->user->ally_id > 0) {
				$alliances = DB::select("SELECT id, username FROM users WHERE ally_id = " . $this->user->ally_id . " AND id != " . $this->user->id . "");

				if (count($alliances)) {
					foreach ($alliances as $user) {
						$parse['alliance'][] = (array) $user;
					}
				}
			}

			$parse['friends'] = [];

			$buddies = DB::select("SELECT u.id, u.username FROM buddy b, users u WHERE u.id = b.sender AND b.owner = " . $this->user->getId() . " AND active = '1'");

			if (count($buddies)) {
				foreach ($buddies as $buddy) {
					$parse['friends'][] = (array) $buddy;
				}
			}
		}

		return $parse;
	}
}
