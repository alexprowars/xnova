<?php
namespace App\Models;

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

	public function initialize()
	{
		$this->useDynamicUpdate(true);
	}

	public function getSource()
	{
		return DB_PREFIX."alliance_members";
	}

	public function onConstruct()
	{
	 	$this->hasOne("a_id", "App\Models\Alliance", "id", Array('alias' => 'alliance'));
	}
}