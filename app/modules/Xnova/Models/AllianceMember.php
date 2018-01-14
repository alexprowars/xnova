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
 * @method static AllianceMember[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static AllianceMember findFirst(mixed $parameters = null)
 */
class AllianceMember extends Model
{
	public $a_id;
	public $u_id;
	public $rank;
	public $time;

	public function getSource()
	{
		return DB_PREFIX."alliance_members";
	}

	public function onConstruct()
	{
	 	$this->hasOne('a_id', 'Xnova\Models\Alliance', 'id', ['alias' => 'alliance']);
	}
}