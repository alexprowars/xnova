<?php

namespace Friday\Core\Models;

use Phalcon\Mvc\Model;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static File[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static File findFirst(mixed $parameters = null)
 */
class File extends Model
{
	public $id;
	public $src;
	public $name;
	public $size;
	public $mime;

	public function getSource()
	{
		return DB_PREFIX."files";
	}
}