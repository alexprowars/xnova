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
 * @method static Queue[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static Queue findFirst(mixed $parameters = null)
 */
class Queue extends Model
{
	public $id;
	public $type;
	public $user_id;
	public $planet_id;
	public $object_id;
	public $level;
	public $operation;
	public $time;
	public $time_end;

	const TYPE_BUILD = 'build';
	const TYPE_TECH = 'tech';
	const TYPE_UNIT = 'unit';

	const OPERATION_BUILD = 'build';
	const OPERATION_DESTROY = 'destroy';

	public function getSource()
	{
		return DB_PREFIX."queue";
	}

	public function beforeCreate ()
	{
		if (!$this->operation)
			$this->operation = self::OPERATION_BUILD;
	}
}