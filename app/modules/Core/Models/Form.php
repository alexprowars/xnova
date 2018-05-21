<?php

namespace Friday\Core\Models;

use Phalcon\Mvc\Model;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static Form[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static Form findFirst(mixed $parameters = null)
 */
class Form extends Model
{
	public $id;
	public $title;

	public function getSource()
	{
		return DB_PREFIX."forms";
	}
}