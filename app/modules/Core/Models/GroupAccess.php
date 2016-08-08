<?php

namespace Friday\Core\Models;

use Phalcon\Mvc\Model;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static GroupAccess[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static GroupAccess findFirst(mixed $parameters = null)
 */
class GroupAccess extends Model
{
	public $id;
	public $group_id;
	public $access_id;

	public function getSource()
	{
		return DB_PREFIX."groups_access";
	}
}