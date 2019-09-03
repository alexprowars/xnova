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

class Usersd
{
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
}