<?php

namespace Xnova\Modelsss;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Mvc\Model;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static Alliance[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static Alliance findFirst(mixed $parameters = null)
 */
class Alliance extends Model
{
	private $rights = [];
	/**
	 * @var AllianceMember $member
	 */
	public $member;

	public function onConstruct()
	{
		$this->hasOne('id', 'Xnova\Models\AllianceMember', 'a_id', ['alias' => 'member']);

		$this->useDynamicUpdate(true);
	}

	public function beforeSave()
	{
		if (is_array($this->ranks))
			$this->ranks = json_encode($this->ranks);
	}

	public function afterSave()
	{
		$this->getRanks();
	}
}