<?php

namespace Friday\Core\Models;

use Phalcon\Mvc\Model;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static Group[]|Model\Resultset find(mixed $parameters = null)
 * @method static Group findFirst(mixed $parameters = null)
 */
class UserGroup extends Model
{
	public $id;
	public $group_id;
	public $user_id;
	public $metadata;

	public function getSource()
	{
		return DB_PREFIX."users_groups";
	}
}