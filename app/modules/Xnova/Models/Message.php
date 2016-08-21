<?php

namespace Xnova\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Mvc\Model;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static Message[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static Message findFirst(mixed $parameters = null)
 */
class Message extends Model
{
	public $id;
	public $owner;
	public $sender;
	public $time;
	public $type;
	public $deleted;
	public $from;
	public $text;

	public function getSource()
	{
		return DB_PREFIX."messages";
	}

	public function beforeCreate ()
	{
		if (!$this->time)
			$this->time = time();
	}
}