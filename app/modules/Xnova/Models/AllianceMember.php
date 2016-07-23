<?php

namespace Xnova\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Mvc\Model;

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