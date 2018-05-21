<?php

namespace Xnova\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Mvc\Model;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static BattleLog[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static BattleLog findFirst(mixed $parameters = null)
 */
class BattleLog extends Model
{
	public $id;
	public $user_id;
	public $time;
	public $title;
	public $log;

	public function getSource()
	{
		return DB_PREFIX."savelog";
	}

	public function beforeCreate ()
	{
		if (!$this->time)
			$this->time = time();
	}
}