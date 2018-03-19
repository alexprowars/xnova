<?php

namespace Xnova;

use Phalcon\Di;
use Xnova\Models\Alliance;
use Xnova\Models\Message;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

class User
{
	public static function sendMessage ($owner, $sender, $time, $type, $from, $message)
	{
		if (!$time)
			$time = time();

		$di = Di::getDefault();

		$user = $di->has('user') ? $di->getShared('user') : false;

		if (!$owner && $user)
			$owner = $user->id;

		if (!$owner)
			return false;

		if ($sender === false && $user)
			$sender = $user->id;

		if ($user && $owner == $user->getId())
			$user->messages++;

		$obj = new Message;

		$obj->user_id = $owner;
		$obj->from_id = $sender;
		$obj->time = $time;
		$obj->type = $type;
		$obj->theme = $from;
		$obj->text = addslashes($message);

		if ($obj->create())
		{
			$di->getShared('db')->updateAsDict(DB_PREFIX.'users', ['+messages' => 1], ['conditions' => 'id = ?', 'bind' => [$owner]]);

			return true;
		}

		return false;
	}

	public static function deleteById ($userId)
	{
		$db = Di::getDefault()->getShared('db');

		$userInfo = $db->query('SELECT id, ally_id FROM '.DB_PREFIX.'users WHERE id = ?0', [(int) $userId])->fetch();

		if (!isset($userInfo['id']))
			return false;

		if ($userInfo['ally_id'] > 0)
		{
			$ally = Alliance::findFirst($userInfo['ally_id']);

			if ($ally)
			{
				if ($ally->owner != $userId)
					$ally->deleteMember($userId);
				else
					$ally->deleteAlly();
			}
		}

		$db->delete(DB_PREFIX.'alliance_requests', 'u_id = ?', [$userId]);
		$db->delete(DB_PREFIX.'statpoints', 'stat_type = 1 AND id_owner = ?', [$userId]);
		$db->delete(DB_PREFIX.'planets', 'id_owner = ?', [$userId]);
		$db->delete(DB_PREFIX.'notes', 'owner = ?', [$userId]);
		$db->delete(DB_PREFIX.'fleets', 'owner = ?', [$userId]);
		$db->delete(DB_PREFIX.'buddy', 'sender = ? OR owner = ?', [$userId, $userId]);
		$db->delete(DB_PREFIX.'refs', 'r_id = ? OR u_id = ?', [$userId, $userId]);
		$db->delete(DB_PREFIX.'log_attack', 'uid = ?', [$userId]);
		$db->delete(DB_PREFIX.'log_credits', 'uid = ?', [$userId]);
		$db->delete(DB_PREFIX.'log_email', 'user_id = ?', [$userId]);
		$db->delete(DB_PREFIX.'log_history', 'user_id = ?', [$userId]);
		$db->delete(DB_PREFIX.'log_transfers', 'user_id = ?', [$userId]);
		$db->delete(DB_PREFIX.'log_username', 'user_id = ?', [$userId]);
		$db->delete(DB_PREFIX.'log_stats', 'id = ?  AND type = 1', [$userId]);
		$db->delete(DB_PREFIX.'logs', 's_id = ? OR e_id = ?', [$userId, $userId]);
		$db->delete(DB_PREFIX.'messages', 'sender = ? OR owner = ?', [$userId, $userId]);
		$db->delete(DB_PREFIX.'banned', 'who = ?', [$userId]);
		$db->delete(DB_PREFIX.'log_ip', 'id = ?', [$userId]);
		$db->delete(DB_PREFIX.'users_tech', 'user_id = ?', [$userId]);

		$update = [
			'authlevel' => 0,
			'group_id' => 0,
			'banned' => 0,
			'planet_id' => 0,
			'planet_current' => 0,
			'bonus' => 0,
			'ally_id' => 0,
			'ally_name' => '',
			'lvl_minier' => 1,
			'lvl_raid' => 1,
			'xpminier' => 0,
			'xpraid' => 0,
			'messages' => 0,
			'messages_ally' => 0,
			'galaxy' => 0,
			'system' => 0,
			'planet' => 0,
			'vacation' => 0,
			'deltime' => 0,
			'b_tech_planet' => 0,
			'raids_win' => 0,
			'raids_lose' => 0,
			'raids' => 0,
			'bonus_multi' => 0,
			'message_block' => 0
		];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) AS $oId)
			$update[Vars::getName($oId)] = 0;

		$db->updateAsDict(DB_PREFIX.'users',
			$update,
			['conditions' => 'id = ?', 'bind' => [$userId]]
		);

		return true;
	}

	public static function getRankId ($lvl)
	{
		if ($lvl <= 1)
			$lvl = 0;

		if ($lvl <= 80)
			return (ceil($lvl / 4) + 1);
		else
			return 22;
	}

	public static function getPlanets ($userId, $moons = true, $allyId = 0)
	{
		if (!$userId)
			return [];

		$qryPlanets = "SELECT id, name, image, galaxy, system, planet, planet_type, destruyed FROM game_planets WHERE id_owner = '" . $userId . "' ";

		$qryPlanets .= ($allyId > 0 ? " OR id_ally = '".$allyId."'" : "");

		if (!$moons)
			$qryPlanets .= " AND planet_type != 3 ";

		/**
		 * @var $user Models\User
		 */
		$user = Di::getDefault()->getShared('user');

		$qryPlanets .= ' ORDER BY '.self::getPlanetListSortQuery($user->planet_sort, $user->planet_sort_order);

		return Di::getDefault()->getShared('db')->query($qryPlanets)->fetchAll();
	}

	public static function getPlanetsId ($userId)
	{
		$result = [];

		/** @var $db Database */
		$db = Di::getDefault()->getShared('db');

		$rows = $db->query('SELECT id FROM game_planets WHERE id_owner = ?', [(int) $userId]);

		while ($row = $rows->fetch())
			$result[] = (int) $row['id'];

		return $result;
	}

	public static function getPlanetListSortQuery ($sort = '', $order = 0)
	{
		$qryPlanets = ' ';

		switch ($sort)
		{
			case 1:
				$qryPlanets .= "galaxy, system, planet, planet_type";
				break;
			case 2:
				$qryPlanets .= "name";
				break;
			case 3:
				$qryPlanets .= "planet_type";
				break;
			default:
				$qryPlanets .= "id";
		}

		$qryPlanets .= ($order == 1) ? " DESC" : " ASC";

		return $qryPlanets;
	}

	public static function checkLevel (Models\User $user)
	{
		$config = Di::getDefault()->getShared('config');
		$url = Di::getDefault()->getShared('url');

		$indNextXp = pow($user->lvl_minier, 3);
		$warNextXp = pow($user->lvl_raid, 2);

		$giveCredits = 0;

		if ($user->xpminier >= $indNextXp && $user->lvl_minier < $config->level->get('max_ind', 100))
		{
			$user->saveData(
			[
				'+lvl_minier' 	=> 1,
				'+credits' 		=> $config->level->get('credits', 10),
				'-xpminier' 	=> $indNextXp
			]);

			User::sendMessage($user->getId(), 0, 0, 1, '', '<a href="'.$url->get('officier/').'">Получен новый промышленный уровень</a>');

			$user->lvl_minier += 1;
			$user->xpminier 	-= $indNextXp;

			$giveCredits += $config->level->get('credits', 10);
		}

		if ($user->xpraid >= $warNextXp && $user->lvl_raid < $config->level->get('max_war', 100))
		{
			$user->saveData(
			[
				'+lvl_raid' => 1,
				'+credits' 	=> $config->level->get('credits', 10),
				'-xpraid' 	=> $warNextXp
			]);

			User::sendMessage($user->getId(), 0, 0, 1, '', '<a href="'.$url->get('officier/').'">Получен новый военный уровень</a>');

			$user->lvl_raid 	+= 1;
			$user->xpraid 	-= $warNextXp;

			$giveCredits += $config->level->get('credits', 10);
		}

		if ($giveCredits != 0)
		{
			$db = Di::getDefault()->getShared('db');

			$user->credits += $giveCredits;

			$db->insertAsDict(
				"game_log_credits",
				[
					'uid' 		=> $user->getId(),
					'time' 		=> time(),
					'credits' 	=> $giveCredits,
					'type' 		=> 4,
				]);

			$reffer = $db->query("SELECT u_id FROM game_refs WHERE r_id = " . $user->getId())->fetch();

			if (isset($reffer['u_id']))
			{
				$db->query("UPDATE game_users SET credits = credits + " . round($giveCredits / 2) . " WHERE id = " . $reffer['u_id'] . "");
				$db->query("INSERT INTO game_log_credits (uid, time, credits, type) VALUES (" . $reffer['u_id'] . ", " . time() . ", " . round($giveCredits / 2) . ", 3)");
			}
		}
	}
}