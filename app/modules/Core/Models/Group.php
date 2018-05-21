<?php

namespace Friday\Core\Models;

use Phalcon\Mvc\Model;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static Group[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static Group findFirst(mixed $parameters = null)
 */
class Group extends Model
{
	public $id;
	public $title;
	public $locked;
	public $metadata;

	const ROLE_ADMIN = 1;
	const ROLE_ANONYM = 2;
	const ROLE_USER = 3;

	public function getSource()
	{
		return DB_PREFIX."groups";
	}

	public function isSystem ()
	{
		return ($this->id <= 3);
	}

	public function getUserGroup ($userId)
	{
		return UserGroup::findFirst(["conditions" => "group_id = ?0 AND user_id = ?0", "bind" => [$this->id, $userId]]);
	}
}