<?php

namespace Xnova\Controllers\Fleet;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controllers\FleetController;
use Friday\Core\Lang;
use Xnova\Models\Fleet;
use Xnova\Models\User;

class Verband
{
	public function show (FleetController $controller)
	{
		$parse = [];

		Lang::includeLang('fleet', 'xnova');

		$fleetid = $controller->request->getPost('fleetid', 'int');

		if (!is_numeric($fleetid) || empty($fleetid) || $fleetid == 0)
			return $controller->response->redirect("overview/");

		$fleet = Fleet::findFirst(['conditions' => 'id = ?0 AND owner = ?1 AND mission = ?2', 'bind' => [$fleetid, $controller->user->id, 1]]);

		if (!$fleet)
			$controller->message('Этот флот не существует!', 'Ошибка');

		$aks = $controller->db->fetchOne("SELECT * FROM game_aks WHERE id = '" . $fleet->group_id . "'");

		if ($fleet->start_time <= time() || $fleet->end_time < time() || $fleet->mess == 1)
			$controller->message('Ваш флот возвращается на планету!', 'Ошибка');

		if ($controller->request->hasPost('action'))
		{
			$action = $controller->request->getPost('action');

			if ($action == 'addaks')
			{
				if (!$fleet->group_id)
				{
					$controller->db->insertAsDict('game_aks',
					[
						'name' 			=> $controller->request->getPost('groupname', 'string'),
						'fleet_id' 		=> $fleet->id,
						'galaxy' 		=> $fleet->end_galaxy,
						'system' 		=> $fleet->end_system,
						'planet' 		=> $fleet->end_planet,
						'planet_type' 	=> $fleet->end_type,
						'user_id' 		=> $controller->user->id,
					]);

					$aksid = $controller->db->lastInsertId();

					if (!$aksid)
						$controller->message('Невозможно получить идентификатор САБ атаки', 'Ошибка');

					$aks = $controller->db->query("SELECT * FROM game_aks WHERE id = '" . $aksid . "'")->fetch();

					/*if ($this->user->data['ally_id'] > 0)
					{
						$allyMembers = $this->db->query("SELECT u_id FROM game_alliance_members WHERE a_id = ".$this->user->data['ally_id']."");

						while ($member = db::fetch($allyMembers))
						{
							$this->db->query("INSERT INTO game_aks_user VALUES (" . $aks['id'] . ", " . $member['u_id'] . ")");
						}
					}*/

					$fleet->group_id = $aksid;
					$fleet->update();
				}
				else
					$controller->message('Для этого флота уже задана ассоциация!', 'Ошибка');
			}
			elseif ($action == 'adduser')
			{
				if ($aks['fleet_id'] != $fleet->id)
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

				User::sendMessage($user_data['id'], false, 0, 0, 'Флот', $message);
			}
			elseif ($action == "changename")
			{
				if ($aks['fleet_id'] != $fleet->id)
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

		if ($fleet->group_id == 0)
			$fq = Fleet::find(['conditions' => 'id = ?0', 'bind' => [$fleet->id]]);
		else
			$fq = Fleet::find(['conditions' => 'group_id = ?0', 'bind' => [$fleet->group_id]]);

		$parse['group'] = $fleet->group_id;
		$parse['fleetid'] = $fleet->id;
		$parse['aks'] = $aks;
		$parse['list'] = $fq;

		if ($fleet->id == $aks['fleet_id'])
		{
			$parse['users'] = [];

			$query = $controller->db->query("SELECT game_users.username FROM game_users, game_aks_user WHERE game_users.id = game_aks_user.user_id AND game_aks_user.aks_id = " . $fleet->group_id . "");

			while ($us = $query->fetch())
				$parse['users'][] = $us['username'];

			$parse['alliance'] = [];

			if ($controller->user->ally_id > 0)
			{
				$alliances = $controller->db->query("SELECT id, username FROM game_users WHERE ally_id = ".$controller->user->ally_id." AND id != ".$controller->user->id."");

				if ($alliances->numRows() > 0)
				{
					while ($user = $alliances->fetch())
						$parse['alliance'][] = $user;
				}
			}

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

		return true;
	}
}