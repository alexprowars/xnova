<?php

namespace Xnova\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Database;
use Phalcon\Mvc\Model;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static UserQuest[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static UserQuest findFirst(mixed $parameters = null)
 * @method Database getWriteConnection
 */
class UserQuest extends Model
{
	public $id;
	public $user_id;
	public $quest_id;
	public $finish;
	public $stage;

	public function getSource()
	{
		return DB_PREFIX."users_quests";
	}
}