<?php

namespace Xnova\Controllers\Fleet;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controllers\FleetController;
use Friday\Core\Lang;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Models\Fleet;
use Xnova\User;

class Verband
{
	public function show (FleetController $controller)
	{
		Lang::includeLang('fleet', 'xnova');

		$fleetId = (int) $controller->request->get('id', 'int');

		if ($fleetId <= 0)
			throw new RedirectException('Флот не выбран', '', 'fleet/');

		$fleet = Fleet::findFirst(['conditions' => 'id = ?0 AND owner = ?1 AND mission = ?2', 'bind' => [$fleetId, $controller->user->id, 1]]);

		if (!$fleet)
			throw new ErrorException('Этот флот не существует!');

		$aks = $controller->db->query("SELECT * FROM game_aks WHERE id = :id", ['id' => $fleet->group_id])->fetch();

		if ($fleet->start_time <= time() || $fleet->end_time < time() || $fleet->mess == 1)
			throw new ErrorException('Ваш флот возвращается на планету!');

		if ($controller->request->hasPost('action'))
		{
			$action = $controller->request->getPost('action');

			if ($action == 'add')
			{
				if ($fleet->group_id)
					throw new ErrorException('Для этого флота уже задана ассоциация!');

				$controller->db->insertAsDict(DB_PREFIX.'aks', [
					'name' 			=> $controller->request->getPost('name', 'string'),
					'fleet_id' 		=> $fleet->id,
					'galaxy' 		=> $fleet->end_galaxy,
					'system' 		=> $fleet->end_system,
					'planet' 		=> $fleet->end_planet,
					'planet_type' 	=> $fleet->end_type,
					'user_id' 		=> $controller->user->id,
				]);

				$id = $controller->db->lastInsertId();

				if (!$id)
					throw new ErrorException('Невозможно получить идентификатор САБ атаки');

				$controller->db->insertAsDict(DB_PREFIX.'aks_user', [
					'aks_id'	=> $id,
					'user_id'	=> $controller->user->id
				]);

				$aks = $controller->db->query("SELECT * FROM game_aks WHERE id = :id", ['id' => $id])->fetch();

				$fleet->group_id = $id;
				$fleet->update();
			}
			elseif ($action == 'adduser')
			{
				if ($aks['fleet_id'] != $fleet->id)
					throw new ErrorException("Вы не можете добавлять сюда игроков");

				$user_data = false;

				$byId = (int) $controller->request->getPost('user_id', 'int');

				if ($byId > 0)
					$user_data = $controller->db->fetchOne("SELECT * FROM game_users WHERE id = '" . $controller->request->getPost('user_id', 'int') . "'");

				$byName = trim($controller->request->getPost('user_name', 'string'));

				if ($byName != '')
					$user_data = $controller->db->query("SELECT * FROM game_users WHERE username = :name", ['name' => $byName])->fetch();

				if (!$user_data)
					throw new ErrorException("Игрок не найден");

				$aks_user = $controller->db->query("SELECT * FROM game_aks_user WHERE aks_id = " . $aks['id'] . " AND user_id = " . $user_data['id'] . "");

				if ($aks_user->numRows() > 0)
					throw new ErrorException("Игрок уже приглашён для нападения");

				$controller->db->insertAsDict('game_aks_user', [
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
					throw new ErrorException("Вы не можете менять имя ассоциации");

				$name = $controller->request->getPost('name', 'string');

				if (mb_strlen($name) < 5)
					throw new ErrorException("Слишком короткое имя ассоциации");

				if (mb_strlen($name) > 20)
					throw new ErrorException("Слишком длинное имя ассоциации");

				if (!preg_match("/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u", $name))
					throw new ErrorException("Имя ассоциации содержит запрещённые символы", _getText('error'));

				$name = strip_tags($name);

				$x = $controller->db->query("SELECT * FROM game_aks WHERE name = :name", ['name' => $name]);

				if ($x->numRows() > 0)
					throw new ErrorException("Имя уже зарезервировано другим игроком");

				$aks['name'] = $name;

				$controller->db->updateAsDict('game_aks', ['name' => $name], 'id = '.$aks['id']);
			}
		}

		if ($fleet->group_id == 0)
			$fq = Fleet::find(['conditions' => 'id = ?0', 'bind' => [$fleet->id]]);
		else
			$fq = Fleet::find(['conditions' => 'group_id = ?0', 'bind' => [$fleet->group_id]]);

		$parse = [];
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
	}
}