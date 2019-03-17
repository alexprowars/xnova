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
 * @method static Rw[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static Rw findFirst(mixed $parameters = null)
 */
class Rw extends Model
{
	public $id;
	public $id_users;
	public $raport;
	public $no_contact;
	public $time;

	public function getSource()
	{
		return DB_PREFIX."rw";
	}
}