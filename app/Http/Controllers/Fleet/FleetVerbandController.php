<?php

namespace Xnova\Http\Controllers\Fleet;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Xnova\Controller;
use Xnova\Exceptions\ErrorException;
use Xnova\Models\Aks;
use Xnova\Models\AksUser;
use Xnova\Models\Fleet;
use Xnova\User;

class FleetVerbandController extends Controller
{
	public function index ($fleetId)
	{
		$fleetId = (int) $fleetId;

		if ($fleetId <= 0)
			throw new ErrorException('Флот не выбран');

		/** @var Fleet $fleet */
		$fleet = Fleet::query()
			->where('id', $fleetId)
			->where('owner', $this->user->id)
			->where('mission', 1)
			->first();

		if (!$fleet)
			throw new ErrorException('Этот флот не существует!');

		$aks = $this->db->query("SELECT * FROM aks WHERE id = :id", ['id' => $fleet->group_id])->fetch();

		if ($fleet->start_time <= time() || $fleet->end_time < time() || $fleet->mess == 1)
			throw new ErrorException('Ваш флот возвращается на планету!');

		if (Input::has('action'))
		{
			$action = Input::post('action');

			if ($action == 'add')
			{
				if ($fleet->group_id)
					throw new ErrorException('Для этого флота уже задана ассоциация!');

				/** @var Aks $aks */
				$aks = Aks::query()->create([
					'name' 			=> Input::post('name', 'string'),
					'fleet_id' 		=> $fleet->id,
					'galaxy' 		=> $fleet->end_galaxy,
					'system' 		=> $fleet->end_system,
					'planet' 		=> $fleet->end_planet,
					'planet_type' 	=> $fleet->end_type,
					'user_id' 		=> $this->user->id,
				]);

				if (!$aks)
					throw new ErrorException('Невозможно получить идентификатор САБ атаки');

				AksUser::query()->create([
					'aks_id'	=> $aks->id,
					'user_id'	=> $this->user->id
				]);

				$fleet->group_id = $aks->id;
				$fleet->update();
			}
			elseif ($action == 'adduser')
			{
				if ($aks['fleet_id'] != $fleet->id)
					throw new ErrorException("Вы не можете добавлять сюда игроков");

				$user_data = false;

				$byId = (int) Input::post('user_id', 'int');

				if ($byId > 0)
					$user_data = $this->db->fetchOne("SELECT * FROM users WHERE id = '" . Input::post('user_id', 'int') . "'");

				$byName = trim(Input::post('user_name', 'string'));

				if ($byName != '')
					$user_data = $this->db->query("SELECT * FROM users WHERE username = :name", ['name' => $byName])->fetch();

				if (!$user_data)
					throw new ErrorException("Игрок не найден");

				$aks_user = $this->db->query("SELECT * FROM aks_user WHERE aks_id = " . $aks['id'] . " AND user_id = " . $user_data['id'] . "");

				if ($aks_user->numRows() > 0)
					throw new ErrorException("Игрок уже приглашён для нападения");

				DB::table('aks_user')->insert([
					'aks_id' 	=> $aks['id'],
					'user_id' 	=> $user_data['id']
				]);

				$planet_daten = $this->db->fetchOne("SELECT `id_owner`, `name` FROM planets WHERE galaxy = '" . $aks['galaxy'] . "' AND system = '" . $aks['system'] . "' AND planet = '" . $aks['planet'] . "' AND planet_type = '" . $aks['planet_type'] . "'");
				$owner = $this->db->fetchOne("SELECT username FROM users WHERE id = '" . $planet_daten['id_owner'] . "'");

				$message = "Игрок " . $this->user->username . " приглашает вас произвести совместное нападение на планету " . $planet_daten['name'] . " [" . $aks['galaxy'] . ":" . $aks['system'] . ":" . $aks['planet'] . "] игрока " . $owner['username'] . ". Имя ассоциации: " . $aks['name'] . ". Если вы отказываетесь, то просто проигнорируйте данной сообщение.";

				User::sendMessage($user_data['id'], false, 0, 0, 'Флот', $message);
			}
			elseif ($action == "changename")
			{
				if ($aks['fleet_id'] != $fleet->id)
					throw new ErrorException("Вы не можете менять имя ассоциации");

				$name = Input::post('name', 'string');

				if (mb_strlen($name) < 5)
					throw new ErrorException("Слишком короткое имя ассоциации");

				if (mb_strlen($name) > 20)
					throw new ErrorException("Слишком длинное имя ассоциации");

				if (!preg_match("/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u", $name))
					throw new ErrorException("Имя ассоциации содержит запрещённые символы");

				$name = strip_tags($name);

				$x = $this->db->query("SELECT * FROM aks WHERE name = :name", ['name' => $name]);

				if ($x->numRows() > 0)
					throw new ErrorException("Имя уже зарезервировано другим игроком");

				$aks['name'] = $name;

				$this->db->updateAsDict('aks', ['name' => $name], 'id = '.$aks['id']);
			}
		}

		if ($fleet->group_id == 0)
			$fq = Fleet::query()->where('id', $fleet->id)->get();
		else
			$fq = Fleet::query()->where('group_id', $fleet->group_id)->get();

		if ($aks)
			$aks['fleet_id'] = (int) $aks['fleet_id'];

		$parse = [];
		$parse['group'] = (int) $fleet->group_id;
		$parse['fleetid'] = (int) $fleet->id;
		$parse['aks'] = $aks;
		$parse['list'] = [];

		foreach ($fq as $row)
		{
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

		if ($fleet->id == $aks['fleet_id'])
		{
			$parse['users'] = [];

			$query = $this->db->query("SELECT users.username FROM users, aks_user WHERE users.id = aks_user.user_id AND aks_user.aks_id = " . $fleet->group_id . "");

			while ($us = $query->fetch())
				$parse['users'][] = $us['username'];

			$parse['alliance'] = [];

			if ($this->user->ally_id > 0)
			{
				$alliances = $this->db->query("SELECT id, username FROM users WHERE ally_id = ".$this->user->ally_id." AND id != ".$this->user->id."");

				if ($alliances->numRows() > 0)
				{
					while ($user = $alliances->fetch())
						$parse['alliance'][] = $user;
				}
			}

			$parse['friends'] = [];

			$buddies = $this->db->query("SELECT u.id, u.username FROM buddy b, users u WHERE u.id = b.sender AND b.owner = ".$this->user->getId()." AND active = '1'");

			if ($buddies->numRows() > 0)
			{
				while ($buddy = $buddies->fetch())
					$parse['friends'][] = $buddy;
			}
		}

		$this->setTitle("Совместная атака");

		return $parse;
	}
}