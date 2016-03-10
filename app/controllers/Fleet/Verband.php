<?php
namespace App\Controllers\Fleet;

use App\Controllers\FleetController;
use App\Fleet;
use App\Lang;

class Verband
{
	public function show (FleetController $controller)
	{
		$parse = [];

		Lang::includeLang('fleet');

		$fleetid = $controller->request->getPost('fleetid', 'int');

		if (!is_numeric($fleetid) || empty($fleetid) || $fleetid == 0)
		{
			$controller->response->redirect("/overview/");
			return;
		}

		$fleet = $controller->db->fetchOne("SELECT * FROM game_fleets WHERE fleet_id = '" . $fleetid . "' AND fleet_owner = " . $controller->user->id . " AND fleet_mission = 1");

		if (!isset($fleet['fleet_id']))
			$controller->message('Этот флот не существует!', 'Ошибка');

		$aks = $controller->db->fetchOne("SELECT * FROM game_aks WHERE id = '" . $fleet['fleet_group'] . "' LIMIT 1");

		if ($fleet['fleet_start_time'] <= time() || $fleet['fleet_end_time'] < time() || $fleet['fleet_mess'] == 1)
			$controller->message('Ваш флот возвращается на планету!', 'Ошибка');

		if ($controller->request->hasPost('action'))
		{
			$action = $controller->request->getPost('action');

			if ($action == 'addaks')
			{
				if (empty($fleet['fleet_group']))
				{
					$controller->db->insertAsDict('game_aks',
					[
						'name' 			=> $controller->request->getPost('groupname', 'string'),
						'fleet_id' 		=> $fleetid,
						'galaxy' 		=> $fleet['fleet_end_galaxy'],
						'system' 		=> $fleet['fleet_end_system'],
						'planet' 		=> $fleet['fleet_end_planet'],
						'planet_type' 	=> $fleet['fleet_end_type'],
						'user_id' 		=> $controller->user->id,
					]);

					$aksid = $controller->db->lastInsertId();

					if (!$aksid)
						$controller->message('Невозможно получить идентификатор САБ атаки', 'Ошибка');

					$aks = $controller->db->query("SELECT * FROM game_aks WHERE id = '" . $aksid . "' LIMIT 1")->fetch();

					/*if ($this->user->data['ally_id'] > 0)
					{
						$allyMembers = $this->db->query("SELECT u_id FROM game_alliance_members WHERE a_id = ".$this->user->data['ally_id']."");

						while ($member = db::fetch($allyMembers))
						{
							$this->db->query("INSERT INTO game_aks_user VALUES (" . $aks['id'] . ", " . $member['u_id'] . ")");
						}
					}*/

					$fleet['fleet_group'] = $aksid;

					$controller->db->updateAsDict('game_fleets', ['fleet_group' => $fleet['fleet_group']], 'fleet_id = '.$fleetid);
				}
				else
					$controller->message('Для этого флота уже задана ассоциация!', 'Ошибка');
			}
			elseif ($action == 'adduser')
			{
				if ($aks['fleet_id'] != $fleetid)
					$controller->message("Вы не можете менять имя ассоциации", 'Ошибка');

				if ($controller->request->hasPost('userid'))
					$user_data = $controller->db->fetchOne("SELECT * FROM game_users WHERE id = '" . $controller->request->getPost('userid', 'int') . "'");
				else
					$user_data = $controller->db->fetchOne("SELECT * FROM game_users WHERE username = '" . $_POST['addtogroup'] . "'");

				if (!isset($user_data['id']))
					$controller->message("Игрок не найден");

				$aks_user = $controller->db->query("SELECT * FROM game_aks_user WHERE aks_id = " . $aks['id'] . " AND user_id = " . $user_data['id'] . "");

				if ($aks_user->numRows() > 0)
					$controller->message("Игрок уже приглашён для нападения", 'Ошибка');

				$controller->db->insertAsDict('game_aks_user',
				[
					'aks_id' 	=> $aks['id'],
					'user_id' 	=> $user_data['id']
				]);

				$planet_daten = $controller->db->fetchOne("SELECT `id_owner`, `name` FROM game_planets WHERE galaxy = '" . $aks['galaxy'] . "' AND system = '" . $aks['system'] . "' AND planet = '" . $aks['planet'] . "' AND planet_type = '" . $aks['planet_type'] . "'");
				$owner = $controller->db->fetchOne("SELECT username FROM game_users WHERE id = '" . $planet_daten['id_owner'] . "'");

				$message = "Игрок " . $controller->user->username . " приглашает вас произвести совместное нападение на планету " . $planet_daten['name'] . " [" . $aks['galaxy'] . ":" . $aks['system'] . ":" . $aks['planet'] . "] игрока " . $owner['username'] . ". Имя ассоциации: " . $aks['name'] . ". Если вы отказываетесь, то просто проигнорируйте данной сообщение.";

				$controller->game->sendMessage($user_data['id'], false, 0, 0, 'Флот', $message);
			}
			elseif ($action == "changename")
			{
				if ($aks['fleet_id'] != $fleetid)
					$controller->message("Вы не можете менять имя ассоциации", 'Ошибка');

				$name = $controller->request->getPost('groupname', 'string');

				if (mb_strlen($name, 'UTF-8') < 5)
					$controller->message("Слишком короткое имя ассоциации", 'Ошибка');

				if (mb_strlen($name, 'UTF-8') > 20)
					$controller->message("Слишком длинное имя ассоциации", 'Ошибка');

				if (!preg_match("/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u", $name))
					$controller->message("Имя ассоциации содержит запрещённые символы", _getText('error'));

				$name = strip_tags($name);

				$x = $controller->db->query("SELECT * FROM game_aks WHERE name = '" . $name . "'");

				if ($x->numRows() >= 1)
					$controller->message("Имя уже зарезервировано другим игроком", 'Ошибка');

				$aks['name'] = $name;

				$controller->db->updateAsDict('game_aks', ['name' => $name], 'id = '.$aks['id']);
			}
		}

		if ($fleet['fleet_group'] == 0)
			$fq = $controller->db->query("SELECT * FROM game_fleets WHERE fleet_id = " . $fleetid . "");
		else
			$fq = $controller->db->query("SELECT * FROM game_fleets WHERE fleet_group = " . $fleet['fleet_group'] . "");

		$parse['group'] = $fleet['fleet_group'];
		$parse['fleetid'] = $fleetid;
		$parse['aks'] = $aks;
		$parse['list'] = [];

		while ($f = $fq->fetch())
		{
			$fleets_count = 0;

			$fleetArray = Fleet::unserializeFleet($f['fleet_array']);

			foreach ($fleetArray as $type)
			{
				$fleets_count += $type['cnt'];
			}

			$f['count'] = $fleets_count;
			$f['fleet'] = $fleetArray;

			$parse['list'][] = $f;
		}


		if ($fleetid == $aks['fleet_id'])
		{
			$parse['users'] = [];

			$query = $controller->db->query("SELECT game_users.username FROM game_users, game_aks_user WHERE game_users.id = game_aks_user.user_id AND game_aks_user.aks_id = " . $fleet['fleet_group'] . "", '');

			while ($us = $query->fetch())
				$parse['users'][] = $us['username'];

			$parse['friends'] = [];

			$buddies = $controller->db->query("SELECT u.id, u.username FROM game_buddy b, game_users u WHERE u.id = b.sender AND b.owner = ".$controller->user->getId()." AND active = '1'");

			if ($buddies->numRows() > 0)
			{
				while ($buddy = $buddies->fetch())
					$parse['friends'][] = $buddy;
			}
		}

		$controller->tag->setTitle("Совместная атака");
		$controller->view->setVar('parse', $parse);
	}
}

?>