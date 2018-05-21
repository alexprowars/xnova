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
 * @method static Note[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static Note findFirst(mixed $parameters = null)
 */
class Note extends Model
{
	public $id;
	public $user_id;
	public $time;
	public $priority;
	public $title;
	public $text;

	public function getSource()
	{
		return DB_PREFIX."notes";
	}

	public function beforeCreate ()
	{
		if (!$this->time)
			$this->time = time();
	}
}