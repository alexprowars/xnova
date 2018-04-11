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
 * @method static Support[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static Support findFirst(mixed $parameters = null)
 */
class Support extends Model
{
	public $id;
	public $user_id;
	public $time;
	public $subject;
	public $text;
	public $status;

	public function getSource()
	{
		return DB_PREFIX."support";
	}

	public function beforeCreate ()
	{
		if (!$this->time)
			$this->time = time();
	}
}