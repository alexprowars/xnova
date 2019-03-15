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
 * @method static Buddy[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static Buddy findFirst(mixed $parameters = null)
 */
class Buddy extends Model
{
	public $id;
	public $sender;
	public $owner;
	public $ignor;
	public $active;
	public $text;

	public function getSource()
	{
		return DB_PREFIX."buddy";
	}
}